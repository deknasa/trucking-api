<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpahSupir extends MyModel
{
    use HasFactory;

    protected $table = 'upahsupir';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }


    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("upahsupir with (readuncommitted)"))
        ->select(
            'upahsupir.id',
            'upahsupir.parent_id',
            'kotadari.keterangan as kotadari_id',
            'kotasampai.keterangan as kotasampai_id',
            'upahsupir.jarak',
            'zona.keterangan as zona_id',
            'parameter.memo as statusaktif',
            'upahsupir.tglmulaiberlaku',
            // 'upahsupir.tglakhirberlaku',
            'statusluarkota.memo as statusluarkota',
            'upahsupir.gambar',
            'upahsupir.created_at',
            'upahsupir.modifiedby',
            'upahsupir.updated_at'
        )
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahsupir.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'upahsupir.statusluarkota', 'statusluarkota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function findAll($id)
    {

        $query = DB::table('upahsupir')->select(
            'upahsupir.id',
            'upahsupir.parent_id',
            'upahsupir.kotadari_id',
            'kotadari.keterangan as kotadari',

            'upahsupir.kotasampai_id',
            'kotasampai.keterangan as kotasampai',
            'upahsupir.jarak',
            'upahsupir.zona_id',
            'zona.keterangan as zona',

            'upahsupir.statusaktif',

            'upahsupir.tglmulaiberlaku',
            // 'upahsupir.tglakhirberlaku',
            'upahsupir.statusluarkota',
            'statusluarkota.text as statusluarkotas',
            'upahsupir.gambar',

            'upahsupir.modifiedby',
            'upahsupir.updated_at'
        )
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'upahsupir.statusluarkota', 'statusluarkota.id')

            ->where('upahsupir.id', $id);

        $data = $query->first();
        return $data;
    }
    public function upahsupirRincian()
    {
        return $this->hasMany(upahsupirRincian::class, 'upahsupir_id');
    }

    public function default()
    {
        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->default(0);
            $table->unsignedBigInteger('statusluarkota')->default(0);
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
            ->where('grp', '=', 'UPAH SUPIR LUAR KOTA')
            ->where('subgrp', '=', 'UPAH SUPIR LUAR KOTA');

        $datadetail = json_decode($status->get(), true);

        $iddefaultstatusluarkota = 0;
        foreach ($datadetail as $item) {
            $memo = json_decode($item['memo'], true);
            $default = $memo['DEFAULT'];

            if ($default == "YA") {
                $iddefaultstatusluarkota = $item['id'];
                break;
            }
        }

        DB::table($tempdefault)->insert(
            ["statusaktif" => $iddefaultstatusaktif,"statusluarkota" => $iddefaultstatusluarkota]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusluarkota',
            );

        $data = $query->first();
        
        return $data;

    }

    public function selectColumns($query)
    {

        return $query->select(
            DB::raw(
                "$this->table.id,
                '$this->table.parent_id',
                kotadari.keterangan as kotadari_id,
                kotasampai.keterangan as kotasampai_id,
                zona.keterangan as zona_id,
                $this->table.jarak,
                $this->table.statusaktif,
                $this->table.tglmulaiberlaku,
                $this->table.statusluarkota,

                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )

        )
        ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
        ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
        ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id');
    }

    public function createTemp(string $modelTable)
    {

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('parent_id')->default('0');
            $table->string('kotadari_id')->default('0');
            $table->string('kotasampai_id')->default('0');
            $table->string('zona_id')->default('0');
            $table->double('jarak', 15, 2)->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->date('tglmulaiberlaku')->default('1900/1/1');
            // $table->date('tglakhirberlaku')->default('1900/1/1');
            $table->integer('statusluarkota')->length(11)->default('0');
            $table->string('modifiedby', 50)->Default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'parent_id', 'kotadari_id', 'kotasampai_id', 'zona_id','jarak', 'statusaktif', 'tglmulaiberlaku','statusluarkota', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
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
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'statusluarkota') {
                            $query = $query->where('statusluarkota.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->where('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kotasampai_id') {
                            $query = $query->where('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'statusluarkota') {
                            $query = $query->orWhere('statusluarkota.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->orWhere('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kotasampai_id') {
                            $query = $query->orWhere('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
