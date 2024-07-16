<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Penerima extends MyModel
{
    use HasFactory;

    protected $table = 'penerima';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {

        $kasGantung = DB::table('kasgantungheader')
            ->from(
                DB::raw("kasgantungheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerima_id'
            )
            ->where('a.penerima_id', '=', $id)
            ->first();
        if (isset($kasGantung)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Kas Gantung',
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

    public function get()
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'penerima.id',
                'penerima.namapenerima',
                'penerima.npwp',
                'penerima.noktp',
                'penerima.keterangan',
                'penerima.modifiedby',
                'parameter_statusaktif.memo as statusaktif',
                'parameter_statuskaryawan.memo as statuskaryawan',
                'penerima.created_at',
                'penerima.updated_at',
                DB::raw("'Laporan Penerima' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'penerima.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statuskaryawan with (readuncommitted)"), 'penerima.statuskaryawan', '=', 'parameter_statuskaryawan.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('penerima.statusaktif', '=', $statusaktif->id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id) 
    {
        $this->setRequestParameters();

        $data = Penerima::from(DB::raw("penerima with (readuncommitted)"))
            ->select(
                'penerima.id',
                'penerima.namapenerima',
                'penerima.npwp',
                'penerima.noktp',
                'penerima.keterangan',
                'penerima.statusaktif',
                'param_aktif.text as statusaktifnama',
                'param_karyawan.text as statuskaryawannama',
                'penerima.modifiedby',
                'penerima.created_at',
                'penerima.updated_at'
            )
            ->leftJoin(DB::raw("parameter as param_aktif with (readuncommitted)"), 'penerima.statusaktif', '=', 'param_aktif.id')
            ->leftJoin(DB::raw("parameter as param_karyawan with (readuncommitted)"), 'penerima.statuskaryawan', '=', 'param_karyawan.id')
            ->where('penerima.id', $id)->first();

        return $data;
    }

    public function default()
    {
        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 300)->nullable();
            $table->unsignedBigInteger('statuskaryawan')->nullable();
            $table->string('statuskaryawannama', 300)->nullable();
        });

        $statusaktif = Parameter::from(
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

        $statuskaryawan = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS KARYAWAN')
            ->where('subgrp', '=', 'STATUS KARYAWAN')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $statusaktif->id ?? 0,
                "statusaktifnama" => $statusaktif->text ?? "",
                "statuskaryawan" => $statuskaryawan->id ?? 0,
                "statuskaryawannama" => $statuskaryawan->text ?? ""
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama',
                'statuskaryawan',
                'statuskaryawannama',
            );

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.namapenerima,
            $this->table.npwp,
            $this->table.noktp,
            $this->table.keterangan,
            'parameter_statusaktif.text as statusaktif',
            'parameter_statuskaryawan.text as statuskaryawan',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'penerima.statusaktif', '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statuskaryawan with (readuncommitted)"), 'penerima.statuskaryawan', '=', 'parameter_statuskaryawan.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('namapenerima', 1000)->nullable();
            $table->string('npwp', 1000)->nullable();
            $table->string('noktp', 1000)->nullable();
            $table->string('keterangan')->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('statuskaryawan', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'namapenerima', 'npwp', 'noktp', 'keterangan', 'statusaktif', 'statuskaryawan', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
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
                                $query = $query->where('parameter_statusaktif.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuskaryawan') {
                                $query = $query->where('parameter_statuskaryawan.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
                                    $query = $query->where('parameter_statusaktif.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'statuskaryawan') {
                                    $query = $query->where('parameter_statuskaryawan.text', '=', "$filters[data]");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function processStore(array $data, Penerima $penerima): Penerima
    {
        // $penerima = new Penerima();
        $penerima->namapenerima = $data['namapenerima'];
        $penerima->npwp = $data['npwp'];
        $penerima->noktp = $data['noktp'];
        $penerima->keterangan = $data['keterangan'];
        $penerima->statusaktif = $data['statusaktif'];
        $penerima->statuskaryawan = $data['statuskaryawan'];
        $penerima->modifiedby = auth('api')->user()->name;
        $penerima->tas_id = $data['tas_id'] ?? '';
        $penerima->info = html_entity_decode(request()->info);


        if (!$penerima->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerima->getTable()),
            'postingdari' => 'ENTRY PENERIMA',
            'idtrans' => $penerima->id,
            'nobuktitrans' => $penerima->id,
            'aksi' => 'ENTRY',
            'datajson' => $penerima->toArray(),
            'modifiedby' => $penerima->modifiedby
        ]);

        return $penerima;
    }

    public function processUpdate(Penerima $penerima, array $data): Penerima
    {
        $penerima->namapenerima = $data['namapenerima'];
        $penerima->npwp = $data['npwp'];
        $penerima->noktp = $data['noktp'];
        $penerima->keterangan = $data['keterangan'];
        $penerima->statusaktif = $data['statusaktif'];
        $penerima->statuskaryawan = $data['statuskaryawan'];
        $penerima->modifiedby = auth('api')->user()->name;
        $penerima->info = html_entity_decode(request()->info);

        if (!$penerima->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerima->getTable()),
            'postingdari' => 'EDIT PENERIMA',
            'idtrans' => $penerima->id,
            'nobuktitrans' => $penerima->id,
            'aksi' => 'EDIT',
            'datajson' => $penerima->toArray(),
            'modifiedby' => $penerima->modifiedby
        ]);

        return $penerima;
    }

    public function processDestroy(Penerima $penerima): Penerima
    {
        // $penerima = new Penerima();
        $penerima = $penerima->lockAndDestroy($penerima->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerima->getTable()),
            'postingdari' => 'DELETE PENERIMA',
            'idtrans' => $penerima->id,
            'nobuktitrans' => $penerima->id,
            'aksi' => 'DELETE',
            'datajson' => $penerima->toArray(),
            'modifiedby' => $penerima->modifiedby
        ]);

        return $penerima;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $penerima = $this->where('id', $data['Id'][$i])->first();

            $penerima->statusaktif = $statusnonaktif->id;
            $penerima->modifiedby = auth('api')->user()->name;
            $penerima->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($penerima->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($penerima->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF Penerima ',
                    'idtrans' => $penerima->id,
                    'nobuktitrans' => $penerima->id,
                    'aksi' => $aksi,
                    'datajson' => $penerima->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $penerima;
    }
}
