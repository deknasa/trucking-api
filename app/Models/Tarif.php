<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Tarif extends MyModel
{
    use HasFactory;

    protected $table = 'tarif';

    protected $casts = [
        'tglberlaku' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $tempUpahsupir = $this->tempUpahsupir();
        $query = Tarif::from(DB::raw("$this->table with (readuncommitted)"))
        ->select(
            'tarif.id',
            'parent.tujuan as parent_id',
            "$tempUpahsupir.kotasampai_id as upahsupir_id",
            'tarif.tujuan',
            'container.keterangan as container_id',
            'tarif.nominal',
            'parameter.memo as statusaktif',
            'sistemton.memo as statussistemton',
            'kota.kodekota as kota_id',
            'zona.zona as zona_id',
            'tarif.nominalton',
            'tarif.tglmulaiberlaku',
            'p.memo as statuspenyesuaianharga',
            'tarif.modifiedby',
            'tarif.created_at',
            'tarif.updated_at'
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarif.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'tarif.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin(DB::raw("$tempUpahsupir with (readuncommitted)"), 'tarif.upahsupir_id', '=', "$tempUpahsupir.id")
            ->leftJoin(DB::raw("tarif as parent with (readuncommitted)"), 'tarif.parent_id', '=', 'parent.id')
            ->leftJoin(DB::raw("parameter AS p with (readuncommitted)"), 'tarif.statuspenyesuaianharga', '=', 'p.id')
            ->leftJoin(DB::raw("parameter AS sistemton with (readuncommitted)"), 'tarif.statussistemton', '=', 'sistemton.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function tempUpahsupir(){
        $tempUpahsupir = '##tempupah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = UpahSupir::from(DB::raw("upahsupir with (readuncommitted)"))
            ->select('upahsupir.id as id','kota.keterangan as kotasampai_id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'upahsupir.kotasampai_id','kota.id');

        Schema::create($tempUpahsupir, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('kotasampai_id', 200)->default('');

        });
        DB::table($tempUpahsupir)->insertUsing(['id', 'kotasampai_id'], $fetch);

        return $tempUpahsupir;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
             $this->table.tujuan,
             container.keterangan as container_id,
             $this->table.nominal,
             parameter.text as statusaktif,
             $this->table.statussistemton,
             kota.kodekota as kota_id,
             zona.zona as zona_id,
             $this->table.nominalton,
             $this->table.tglmulaiberlaku,
             p.text as statuspenyesuaianharga,
             $this->table.modifiedby,
             $this->table.created_at,
             $this->table.updated_at"

            )

        )
            ->leftJoin('parameter', 'tarif.statusaktif', '=', 'parameter.id')
            ->leftJoin('container', 'tarif.container_id', '=', 'container.id')
            ->leftJoin('kota', 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin('zona', 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin('parameter AS p', 'tarif.statuspenyesuaianharga', '=', 'p.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('tujuan', 200)->default('');
            $table->string('container_id')->default('0');
            $table->double('nominal', 15, 2)->default('0');
            $table->string('statusaktif')->default('0');
            $table->integer('statussistemton')->length(11)->default('0');
            $table->string('kota_id')->default('0');
            $table->string('zona_id')->nullable();
            $table->double('nominalton', 15, 2)->default('0');
            $table->date('tglmulaiberlaku')->default('1900/1/1');
            $table->string('statuspenyesuaianharga')->default('0');

            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'tujuan', 'container_id', 'nominal', 'statusaktif',  'statussistemton', 'kota_id', 'zona_id', 'nominalton', 'tglmulaiberlaku', 'statuspenyesuaianharga', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->default(0);
            $table->unsignedBigInteger('statussistemton')->default(0);
            $table->unsignedBigInteger('statuspenyesuaianharga')->default(0);
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'memo',
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF');

        $datadetail = json_decode($status->get(), true);

        $iddefaultstatusaktif = 0;
        foreach ($datadetail as $item) {
            $memo = json_decode($item['memo'], true);
            $default = $memo['DEFAULT'];
            if ($default == "YA") {
                $iddefaultstatusaktif = $item['id'];
                break;
            }
        }

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'memo',
                'id'
            )
            ->where('grp', '=', 'SISTEM TON')
            ->where('subgrp', '=', 'SISTEM TON');

        $datadetail = json_decode($status->get(), true);

        $iddefaultstatussistemton = 0;
        foreach ($datadetail as $item) {
            $memo = json_decode($item['memo'], true);
            $default = $memo['DEFAULT'];

            if ($default == "YA") {
                $iddefaultstatussistemton = $item['id'];
                break;
            }
        }

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'memo',
                'id'
            )
            ->where('grp', '=', 'PENYESUAIAN HARGA')
            ->where('subgrp', '=', 'PENYESUAIAN HARGA');

        $datadetail = json_decode($status->get(), true);

        $iddefaultstatuspenyesuaianharga = 0;
        foreach ($datadetail as $item) {
            $memo = json_decode($item['memo'], true);
            $default = $memo['DEFAULT'];

            if ($default == "YA") {
                $iddefaultstatuspenyesuaianharga = $item['id'];
                break;
            }
        }
        DB::table($tempdefault)->insert(
            ["statusaktif" => $iddefaultstatusaktif,
            "statussistemton" => $iddefaultstatussistemton,
            "statuspenyesuaianharga" => $iddefaultstatuspenyesuaianharga
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statussistemton',
                'statuspenyesuaianharga',
            );

        $data = $query->first();
        
        return $data;
    }

    public function findAll($id)
    {        
        $tempUpahsupir = (new static)->tempUpahsupir();
        $query = Tarif::from(DB::raw("tarif with (readuncommitted)"))
        ->select(
            'tarif.id',
            'tarif.parent_id',
            'parent.tujuan as parent',
            'tarif.upahsupir_id',
            "$tempUpahsupir.kotasampai_id as upahsupir",
            'tarif.tujuan',
            'tarif.container_id',
            'container.keterangan as container',
            'tarif.nominal',
            'tarif.statusaktif',
            'tarif.statussistemton',
            
            'tarif.kota_id',
            'kota.keterangan as kota',
            'tarif.zona_id',
            'zona.keterangan as zona',
            
            'tarif.nominalton',
            'tarif.tglmulaiberlaku',
            'tarif.statuspenyesuaianharga',
        )
            ->leftJoin(DB::raw("container with (readuncommitted)"),'tarif.container_id','container.id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin(DB::raw("tarif as parent with (readuncommitted)"), 'tarif.parent_id', '=', 'parent.id')
            ->leftJoin(DB::raw("$tempUpahsupir with (readuncommitted)"), 'tarif.upahsupir_id', '=', "$tempUpahsupir.id")

            ->where('tarif.id', $id);

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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'container_id') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kota_id') {
                            $query = $query->where('kota.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                            $query = $query->where('p.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'statussistemton') {
                            $query = $query->where('sistemton.text', '=', "$filters[data]");
                        } else {
                            $query = $query->where('tarif.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'container_id') {
                            $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kota_id') {
                            $query = $query->orWhere('kota.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'zona_id') {
                            $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                            $query = $query->orWhere('p.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'statussistemton') {
                            $query = $query->orWhere('sistemton.text', '=', "$filters[data]");
                        } else {
                            $query = $query->orWhere('tarif.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

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
}
