<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TripTangki extends MyModel
{
    use HasFactory;
    protected $table = 'triptangki';

    public function get()
    {
        $this->setRequestParameters();
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';
        $trado_id = request()->trado_id ?? '';
        $supir_id = request()->supir_id ?? '';
        $tglbukti = request()->tglbukti ?? '';
        $from = request()->from ?? '';
        $lookup = boolval(request()->lookup) ?? false;

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'triptangki.id',
                'triptangki.kodetangki',
                'triptangki.keterangan',
                'parameter.memo as statusaktif',
                'triptangki.modifiedby',
                'triptangki.created_at',
                'triptangki.updated_at',
                DB::raw("'Laporan Trip Tangki' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'triptangki.statusaktif', '=', 'parameter.id');

        if ($lookup && $from == 'inputtrip') {
            $jenisTangki = DB::table('parameter')->from(
                DB::raw("parameter as a with (readuncommitted)")
            )
                ->select(
                    'a.id'
                )
                ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
                ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
                ->where('a.text', '=', 'TANGKI')
                ->first();
            $getTripTangki = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(DB::raw("STRING_AGG(triptangki_id, ',') as triptangki"))
                ->where('supir_id', $supir_id)
                ->where('tglbukti', date('Y-m-d', strtotime($tglbukti)))
                ->where('statusjeniskendaraan', $jenisTangki->id)
                ->first()->triptangki;

            if (isset($getTripTangki)) {
                $query->whereRaw("triptangki.kodetangki not in ($getTripTangki)");
            }
        }
        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('triptangki.statusaktif', '=', $statusaktif->id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function cekvalidasihapus($id)
    {
        // cek sudah ada container

        $upahSupir = DB::table('upahsupirtangkirincian')
            ->from(
                DB::raw("upahsupirtangkirincian as a with (readuncommitted)")
            )
            ->select(
                'a.triptangki_id'
            )
            ->where('a.triptangki_id', '=', $id)
            ->first();

        if (isset($upahSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Supir Tangki',
            ];

            goto selesai;
        }

        $suratpengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.triptangki_id'
            )
            ->where('a.triptangki_id', '=', $id)
            ->first();

        if (isset($suratpengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'surat pengantar',
            ];

            goto selesai;
        }

        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];

        selesai:
        return $data;
    }

    public function findAll($id)
    {
        $this->setRequestParameters();

        $data = TripTangki::from(DB::raw("triptangki with (readuncommitted)"))
            ->select(
                'triptangki.id',
                'triptangki.kodetangki',
                'triptangki.keterangan',
                'triptangki.statusaktif',
                'parameter.text as statusaktifnama',
                'triptangki.modifiedby',
                'triptangki.created_at',
                'triptangki.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'triptangki.statusaktif', '=', 'parameter.id')
            ->where('triptangki.id', $id)->first();

        return $data;
    }

    public function default()
    {
        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 300)->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(
            ["statusaktif" => $status->id ?? 0, "statusaktifnama" => $status->text ?? ""]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama'
            );

        $data = $query->first();

        return $data;
    }


    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusaktif') {
                                    $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
                            }
                        }
                    });

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            DB::raw("
                $this->table.id,
                $this->table.kodetangki,
                $this->table.keterangan,
                'parameter.text as statusaktif',
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'triptangki.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodetangki', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'kodetangki', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function processStore(array $data): TripTangki
    {
        $tripTangki = new TripTangki();
        $tripTangki->kodetangki = $data['kodetangki'];
        $tripTangki->keterangan = $data['keterangan'] ?? '';
        $tripTangki->statusaktif = $data['statusaktif'];
        $tripTangki->tas_id = $data['tas_id'] ?? '';
        $tripTangki->modifiedby = auth('api')->user()->name;
        $tripTangki->info = html_entity_decode(request()->info);
        if (!$tripTangki->save()) {
            throw new \Exception("Error storing trip tangki.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tripTangki->getTable()),
            'postingdari' => 'ENTRY TRIP TANGKI',
            'idtrans' => $tripTangki->id,
            'nobuktitrans' => $tripTangki->id,
            'aksi' => 'ENTRY',
            'datajson' => $tripTangki->toArray(),
            'modifiedby' => $tripTangki->modifiedby
        ]);

        return $tripTangki;
    }
    public function processUpdate(TripTangki $tripTangki, array $data): TripTangki
    {

        $tripTangki->kodetangki = $data['kodetangki'];
        $tripTangki->keterangan = $data['keterangan'] ?? '';
        $tripTangki->statusaktif = $data['statusaktif'];
        $tripTangki->modifiedby = auth('api')->user()->name;
        $tripTangki->info = html_entity_decode(request()->info);

        if (!$tripTangki->save()) {
            throw new \Exception("Error update trip tangki.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tripTangki->getTable()),
            'postingdari' => 'EDIT TRIP TANGKI',
            'idtrans' => $tripTangki->id,
            'nobuktitrans' => $tripTangki->id,
            'aksi' => 'EDIT',
            'datajson' => $tripTangki->toArray(),
            'modifiedby' => $tripTangki->modifiedby
        ]);

        return $tripTangki;
    }

    public function processDestroy($id): TripTangki
    {
        $tripTangki = new TripTangki();
        $tripTangki = $tripTangki->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tripTangki->getTable()),
            'postingdari' => 'DELETE TRIP TANGKI',
            'idtrans' => $tripTangki->id,
            'nobuktitrans' => $tripTangki->id,
            'aksi' => 'DELETE',
            'datajson' => $tripTangki->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $tripTangki;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $tripTangki = TripTangki::find($data['Id'][$i]);

            $tripTangki->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($tripTangki->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($tripTangki->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF TRIP TANGKI',
                    'idtrans' => $tripTangki->id,
                    'nobuktitrans' => $tripTangki->id,
                    'aksi' => $aksi,
                    'datajson' => $tripTangki->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $tripTangki;
    }
}
