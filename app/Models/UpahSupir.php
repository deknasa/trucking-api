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


        $aktif = request()->aktif ?? '';
        $tempParent = DB::table($this->table)->from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupir.id',
                'upahsupir.parent_id',
                'kota.keterangan'
            )
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'kota.id', 'upahsupir.kotasampai_id');

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('keterangan')->nullable();
        });
        DB::table($temp)->insertUsing(["id", 'parent_id', 'keterangan'], $tempParent);

        $query = DB::table($this->table)->from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupir.id',
                'parent.keterangan as parent_id',
                'kotadari.keterangan as kotadari_id',
                'kotasampai.keterangan as kotasampai_id',
                'upahsupir.penyesuaian',
                DB::raw("CONCAT(upahsupir.jarak, ' KM') as jarak"),
                'zona.keterangan as zona_id',
                'parameter.memo as statusaktif',
                'upahsupir.tglmulaiberlaku',
                // 'upahsupir.tglakhirberlaku',
                'statusluarkota.memo as statusluarkota',
                'upahsupir.gambar',
                'upahsupir.keterangan',
                'upahsupir.created_at',
                'upahsupir.modifiedby',
                'upahsupir.updated_at'
            )
            ->leftJoin(DB::raw("$temp as parent with (readuncommitted)"), 'parent.id', '=', 'upahsupir.parent_id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'upahsupir.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'upahsupir.statusluarkota', 'statusluarkota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id');

        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('upahsupir.statusaktif', '=', $statusaktif->id);
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

        $tempParent = DB::table('upahsupir')->from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(
                'upahsupir.id',
                'upahsupir.parent_id',
                'kota.keterangan'
            )
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'kota.id', 'upahsupir.kotasampai_id');
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('keterangan')->nullable();
        });
        DB::table($temp)->insertUsing(["id", 'parent_id', 'keterangan'], $tempParent);

        $query = DB::table('upahsupir')->select(
            'upahsupir.id',
            DB::raw("(case when upahsupir.parent_id=0 then null else upahsupir.parent_id end) as parent_id"),
            'parent.keterangan as parent',
            DB::raw("(case when upahsupir.tarif_id=0 then null else upahsupir.tarif_id end) as tarif_id"),
            'tarif.tujuan as tarif',
            'upahsupir.kotadari_id',
            'kotadari.keterangan as kotadari',
            'upahsupir.keterangan',
            'upahsupir.penyesuaian',
            'upahsupir.kotasampai_id',
            'kotasampai.keterangan as kotasampai',
            'upahsupir.jarak',
            'zona.keterangan as zona',
            DB::raw("(case when upahsupir.zona_id=0 then null else upahsupir.zona_id end) as zona_id"),
            'upahsupir.statusaktif',

            'upahsupir.tglmulaiberlaku',
            // 'upahsupir.tglakhirberlaku',
            'upahsupir.statusluarkota',
            'statusluarkota.text as statusluarkotas',
            'upahsupir.gambar',

            'upahsupir.modifiedby',
            'upahsupir.updated_at'
        )
            ->leftJoin(DB::raw("$temp as parent with (readuncommitted)"), 'parent.id', '=', 'upahsupir.parent_id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'upahsupir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
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
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->unsignedBigInteger('statusluarkota')->nullable();
            $table->unsignedBigInteger('statussimpankandang')->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusaktif = $status->id ?? 0;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'UPAH SUPIR LUAR KOTA')
            ->where('subgrp', '=', 'UPAH SUPIR LUAR KOTA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusluarkota = $status->id ?? 0;

        $iddefaultstatusluarkota =  $status->id;
        
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS SIMPAN KANDANG')
            ->where('subgrp', '=', 'STATUS SIMPAN KANDANG')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusSimpanKandang = $status->id ?? 0;

        DB::table($tempdefault)->insert(
            ["statusaktif" => $iddefaultstatusaktif, "statusluarkota" => $iddefaultstatusluarkota, "statussimpankandang" => $iddefaultstatusSimpanKandang]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusluarkota',
                'statussimpankandang'
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
                '$this->table.penyesuaian',
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
            $table->bigInteger('id')->nullable();
            $table->string('parent_id')->nullable();
            $table->string('kotadari_id')->nullable();
            $table->string('kotasampai_id')->nullable();
            $table->string('penyesuaian')->nullable();
            $table->string('zona_id')->nullable()->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            // $table->date('tglakhirberlaku')->nullable();
            $table->integer('statusluarkota')->length(11)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'parent_id', 'kotadari_id', 'kotasampai_id','penyesuaian', 'zona_id', 'jarak', 'statusaktif', 'tglmulaiberlaku', 'statusluarkota', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'kotadari_id') {
            return $query->orderBy('kotadari.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kotasampai_id') {
            return $query->orderBy('kotasampai.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'zona_id') {
            return $query->orderBy('zona.keterangan', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'parent_id') {
                            $query = $query->where('parent.keterangan', '=', $filters['data']);
                        } elseif ($filters['field'] == 'statusluarkota') {
                            $query = $query->where('statusluarkota.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kotadari_id') {
                            $query = $query->where('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kotasampai_id') {
                            $query = $query->where('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'jarak') {
                            $query = $query->whereRaw("format($this->table.jarak, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', $filters['data']);
                            } elseif ($filters['field'] == 'statusluarkota') {
                                $query = $query->orWhere('statusluarkota.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'kotadari_id') {
                                $query = $query->orWhere('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'parent_id') {
                                $query = $query->orWhere('parent.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kotasampai_id') {
                                $query = $query->orWhere('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'zona_id') {
                                $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'jarak') {
                                $query = $query->orWhereRaw("format($this->table.jarak, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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

    public function cekValidasi($id)
    {
        $rekap = DB::table('tarif')
            ->from(
                DB::raw("tarif as a with (readuncommitted)")
            )
            ->select(
                'a.upahsupir_id'
            )
            ->where('a.id', '=', $id)
            ->first();
        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'tarif',
                'kodeerror' => 'SATL'
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
    public function validasiUpahSupirInputTrip($dari, $sampai, $container,$statusContainer){
        $query = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
        ->join(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id','upahsupirrincian.upahsupir_id')
        ->where('upahsupir.kotadari_id', $dari)
        ->where('upahsupir.kotasampai_id', $sampai)
        ->where('upahsupirrincian.container_id', $container)
        ->where('upahsupirrincian.statuscontainer_id', $statusContainer)
        ->where('upahsupirrincian.nominalsupir','!=','0')
        ->first();

        return $query;
    }
}
