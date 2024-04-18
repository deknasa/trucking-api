<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Merk extends MyModel
{
    use HasFactory;

    protected $table = 'merk';

    // protected $casts = [
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    public function cekvalidasihapus($id)
    {

        $stok = DB::table('stok')
            ->from(
                DB::raw("stok as a with (readuncommitted)")
            )
            ->select(
                'a.merk_id'
            )
            ->where('a.merk_id', '=', $id)
            ->first();
        if (isset($stok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Stok',
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
        )->select(
            'merk.id',
            'merk.kodemerk',
            'merk.keterangan',
            'parameter.memo as statusaktif',
            'merk.modifiedby',
            'merk.created_at',
            'merk.updated_at',
            DB::raw("'Laporan Merk' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'merk.statusaktif', '=', 'parameter.id');




        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('merk.statusaktif', '=', $statusaktif->id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'text',
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id,"statusaktifnama" => $statusaktif->text]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function findAll($id) {
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'merk.id',
                'merk.kodemerk',
                'merk.keterangan',
                'parameter.id as statusaktif',
                'parameter.text as statusaktifnama',
                'merk.modifiedby',
                'merk.created_at',
                'merk.updated_at',
            )
            ->where('merk.id',$id)
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'merk.statusaktif', '=', 'parameter.id');
            return $query->first();

    }
    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.kodemerk,
            $this->table.keterangan,
            'parameter.text as statusaktif',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'merk.statusaktif', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodemerk', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodemerk', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where('merk.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('merk' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere('merk.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('merk' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): Merk
    {

        $merk = new Merk();
        $merk->kodemerk = $data['kodemerk'];
        $merk->keterangan = $data['keterangan'] ?? '';
        $merk->statusaktif = $data['statusaktif'];
        $merk->tas_id = $data['tas_id'];
        $merk->modifiedby = auth('api')->user()->name;
        $merk->info = html_entity_decode(request()->info);

        if (!$merk->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($merk->getTable()),
            'postingdari' => 'ENTRY MERK',
            'idtrans' => $merk->id,
            'nobuktitrans' => $merk->id,
            'aksi' => 'ENTRY',
            'datajson' => $merk->toArray(),
            'modifiedby' => $merk->modifiedby
        ]);

        return $merk;
    }

    public function processUpdate(Merk $merk, array $data): Merk
    {
        $merk = Merk::lockForUpdate()->findOrFail($merk->id);
        $merk->kodemerk = $data['kodemerk'];
        $merk->keterangan = $data['keterangan'] ?? '';
        $merk->statusaktif = $data['statusaktif'];
        $merk->modifiedby = auth('api')->user()->name;
        $merk->info = html_entity_decode(request()->info);

        if (!$merk->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($merk->getTable()),
            'postingdari' => 'EDIT MERK',
            'idtrans' => $merk->id,
            'nobuktitrans' => $merk->id,
            'aksi' => 'EDIT',
            'datajson' => $merk->toArray(),
            'modifiedby' => $merk->modifiedby
        ]);

        return $merk;
    }

    public function processDestroy($id): Merk
    {
        $merk = new Merk();
        $merk = $merk->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($merk->getTable()),
            'postingdari' => 'DELETE MERK',
            'idtrans' => $merk->id,
            'nobuktitrans' => $merk->id,
            'aksi' => 'DELETE',
            'datajson' => $merk->toArray(),
            'modifiedby' => $merk->modifiedby
        ]);

        return $merk;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $merk = Merk::find($data['Id'][$i]);

            $merk->statusaktif = $statusnonaktif->id;
            $merk->modifiedby = auth('api')->user()->name;
            $merk->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($merk->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($merk->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF MERK',
                    'idtrans' => $merk->id,
                    'nobuktitrans' => $merk->id,
                    'aksi' => $aksi,
                    'datajson' => $merk->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $merk;
    }
}
