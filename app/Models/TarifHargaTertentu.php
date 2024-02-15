<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TarifHargaTertentu extends MyModel
{
    use HasFactory;
    protected $table = 'tarifhargatertentu';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];


    public function get()
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'tarifhargatertentu.id',
                'tarifhargatertentu.tarif_id',
                'tarif.tujuan',
                'tarif.penyesuaian',
                'container.keterangan as container',
                'tarifhargatertentu.tujuanbongkar',
                'tarifhargatertentu.lokasidooring',
                'tarifhargatertentu.lokasidooring_id',
                'tarifhargatertentu.shipper',
                'tarifhargatertentu.nominal',
                'tarifhargatertentu.cabang',
                'parameter.memo as statuscabang',
                'aktif.memo as statusaktif',
                'tarifhargatertentu.modifiedby',
                'tarifhargatertentu.created_at',
                'tarifhargatertentu.updated_at',
                DB::raw("'Laporan Tarif Harga Tertentu' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),

            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarifhargatertentu.statuscabang', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as aktif with (readuncommitted)"), 'tarifhargatertentu.statusaktif', '=', 'aktif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'tarifhargatertentu.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'tarifhargatertentu.tarif_id', '=', 'tarif.id');



        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('tarifhargatertentu.statusaktif', '=', $statusaktif->id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;


        $this->sort($query);
        $this->paginate($query);
        // dd($query->get());
        $data = $query->get();

        return $data;
    }


    public function findAll($id)
    {
        $query = TarifHargaTertentu::from(DB::raw("tarifhargatertentu with (readuncommitted)"))
            ->select(
                'tarifhargatertentu.id',
                'tarifhargatertentu.tarif_id',
                'tarif.tujuan as tarif',
                'tarif.penyesuaian',
                'tarifhargatertentu.container_id',
                'container.keterangan as container',
                'tarifhargatertentu.tujuanbongkar',
                'tarifhargatertentu.lokasidooring',
                'tarifhargatertentu.lokasidooring_id',
                'tarifhargatertentu.shipper',
                'tarifhargatertentu.nominal',
                'tarifhargatertentu.cabang',
                'parameter.text as statuscabang',
                'tarifhargatertentu.statusaktif as statusaktif',
                'tarifhargatertentu.modifiedby',
                'tarifhargatertentu.created_at',
                'tarifhargatertentu.updated_at',
                DB::raw("'Laporan Tarif Harga Tertentu' as judulLaporan"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarifhargatertentu.statuscabang', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as aktif with (readuncommitted)"), 'tarifhargatertentu.statusaktif', '=', 'aktif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'tarifhargatertentu.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'tarifhargatertentu.tarif_id', '=', 'tarif.id')

            ->where('tarifhargatertentu.id', $id);

        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
                    $this->table.tarif_id,
                    $this->table.container_id,
                    'container.keterangan as container',
                    'tarif.tujuan as tujuan',
            'tarif.penyesuaian as penyesuaian',
            $this->table.tujuanbongkar,
            $this->table.lokasidooring,
            $this->table.lokasidooring_id,
            $this->table.shipper,
            $this->table.nominal,
            $this->table.cabang,
            'parameter.text as statuscabang',
            'aktif.text as statusaktif',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarifhargatertentu.statuscabang', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as aktif with (readuncommitted)"), 'tarifhargatertentu.statusaktif', '=', 'aktif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'tarifhargatertentu.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'tarifhargatertentu.tarif_id', '=', 'tarif.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('container', 500)->nullable();
            $table->string('tujuan', 500)->nullable();
            $table->string('penyesuaian', 500)->nullable();
            $table->string('tujuanbongkar', 500)->nullable();
            $table->string('lokasidooring', 500)->nullable();
            $table->unsignedBigInteger('lokasidooring_id')->nullable();
            $table->string('shipper', 500)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('cabang', 500)->nullable();
            $table->string('statuscabang', 500)->nullable();
            $table->string('statusaktif', 500)->nullable();
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
        DB::table($temp)->insertUsing([
            'id', 'tarif_id', 'container_id', 'container', 'tujuan',
            'penyesuaian', 'tujuanbongkar', 'lokasidooring', 'lokasidooring_id', 'shipper',
            'nominal', 'cabang', 'statuscabang', 'statusaktif',
            'modifiedby', 'created_at', 'updated_at'
        ], $models);

        return  $temp;
    }


    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'statusaktif') {
            return $query->orderBy('aktif.text', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statuscabang') {
            return $query->orderBy('parameter.text', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'container') {
            return $query->orderBy('container.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tujuan') {
            return $query->orderBy('tarif.tujuan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'penyesuaian') {
            return $query->orderBy('tarif.penyesuaian', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'created_at' || $this->params['sortIndex'] == 'updated_at') {
            return $query->orderBy(db::raw("format(" . $this->table . "." . $this->params['sortIndex'] . ", 'dd-MM-yyyy HH:mm:ss') "), $this->params['sortOrder']);
        } else {
            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('aktif.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscabang') {
                                $query = $query->where('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'container') {
                                $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tujuan') {
                                $query = $query->where('tarif.tujuan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'penyesuaian') {
                                $query = $query->where('tarif.penyesuaian', 'LIKE', "%$filters[data]%");
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
                                    $query = $query->orWhere('aktif.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscabang') {
                                    $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'container') {
                                    $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");;
                                } else if ($filters['field'] == 'tujuan') {
                                    $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");;
                                } else if ($filters['field'] == 'penyesuaian') {
                                    $query = $query->orWhere('tarif.penyesuaian', 'LIKE', "%$filters[data]%");;
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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

    public function processStore(array $data): TarifHargaTertentu
    {

        $statuscabang = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.grp', 'STATUS CABANG')
            ->where('a.subgrp', 'STATUS CABANG')
            ->where('a.text', $data['statuscabang'])
            ->first()->id ?? 0;


        $tarifHargaTertentu = new TarifHargaTertentu();
        $tarifHargaTertentu->tarif_id = $data['tarif_id'];
        $tarifHargaTertentu->container_id = $data['container_id'];
        $tarifHargaTertentu->tujuanbongkar = $data['tujuanbongkar'];
        $tarifHargaTertentu->lokasidooring = $data['lokasidooring'];
        $tarifHargaTertentu->lokasidooring_id = $data['lokasidooring_id'];
        $tarifHargaTertentu->shipper = $data['shipper'];
        $tarifHargaTertentu->nominal = $data['nominal'];
        $tarifHargaTertentu->cabang = $data['cabang'];
        $tarifHargaTertentu->statuscabang = $statuscabang;
        $tarifHargaTertentu->statusaktif = $data['statusaktif'];
        $tarifHargaTertentu->modifiedby = auth('api')->user()->user;
        $tarifHargaTertentu->info = html_entity_decode(request()->info);


        if (!$tarifHargaTertentu->save()) {
            throw new \Exception('Error storing Tarif HARGA TERTENTU.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarifHargaTertentu->getTable()),
            'postingdari' => 'ENTRY TARIF HARGA TERTENTU',
            'idtrans' => $tarifHargaTertentu->id,
            'nobuktitrans' => $tarifHargaTertentu->id,
            'aksi' => 'ENTRY',
            'datajson' => $tarifHargaTertentu->toArray(),
            'modifiedby' => $tarifHargaTertentu->modifiedby
        ]);

        return $tarifHargaTertentu;
    }


    public function processUpdate(TarifHargaTertentu $tarifHargaTertentu, array $data): TarifHargaTertentu
    {

        $statuscabang = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.grp', 'STATUS CABANG')
            ->where('a.subgrp', 'STATUS CABANG')
            ->where('a.text', $data['statuscabang'])
            ->first()->id ?? 0;

        $tarifHargaTertentu->tarif_id = $data['tarif_id'];
        $tarifHargaTertentu->container_id = $data['container_id'];
        $tarifHargaTertentu->tujuanbongkar = $data['tujuanbongkar'];
        $tarifHargaTertentu->lokasidooring = $data['lokasidooring'];
        $tarifHargaTertentu->lokasidooring_id = $data['lokasidooring_id'];
        $tarifHargaTertentu->shipper = $data['shipper'];
        $tarifHargaTertentu->nominal = $data['nominal'];
        $tarifHargaTertentu->cabang = $data['cabang'];
        $tarifHargaTertentu->statuscabang = $statuscabang;
        $tarifHargaTertentu->statusaktif = $data['statusaktif'];
        $tarifHargaTertentu->modifiedby = auth('api')->user()->user;
        $tarifHargaTertentu->info = html_entity_decode(request()->info);

        if (!$tarifHargaTertentu->save()) {
            throw new \Exception('Error updating tarif harga tertentu.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarifHargaTertentu->getTable()),
            'postingdari' => 'EDIT TARIF HARGA TERTENTU',
            'idtrans' => $tarifHargaTertentu->id,
            'nobuktitrans' => $tarifHargaTertentu->id,
            'aksi' => 'EDIT',
            'datajson' => $tarifHargaTertentu->toArray(),
            'modifiedby' => $tarifHargaTertentu->modifiedby
        ]);

        return $tarifHargaTertentu;
    }


    public function processDestroy($id): TarifHargaTertentu
    {
        $tarifHargaTertentu = new TarifHargaTertentu();
        $tarifHargaTertentu = $tarifHargaTertentu->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarifHargaTertentu->getTable()),
            'postingdari' => 'DELETE TARIF HARGA TERTENTU',
            'idtrans' => $tarifHargaTertentu->id,
            'nobuktitrans' => $tarifHargaTertentu->id,
            'aksi' => 'DELETE',
            'datajson' => $tarifHargaTertentu->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $tarifHargaTertentu;
    }


    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->unsignedBigInteger('statuscabang')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $statuscabang = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS CABANG')
            ->where('subgrp', '=', 'STATUS CABANG')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $statusaktif->id,
                "statuscabang" => $statuscabang->id,


            ],
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $tarifHargaTertentu = TarifHargaTertentu::find($data['Id'][$i]);

            $tarifHargaTertentu->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($tarifHargaTertentu->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($tarifHargaTertentu->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF TARIF HARGA TERTENTU',
                    'idtrans' => $tarifHargaTertentu->id,
                    'nobuktitrans' => $tarifHargaTertentu->id,
                    'aksi' => $aksi,
                    'datajson' => $tarifHargaTertentu->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $tarifHargaTertentu;
    }
}
