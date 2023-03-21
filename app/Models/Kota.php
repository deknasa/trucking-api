<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Kota extends MyModel
{
    use HasFactory;

    protected $table = 'kota';

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
        $tarif = DB::table('tarif')
            ->from(
                DB::raw("tarif as a with (readuncommitted)")
            )
            ->select(
                'a.kota_id'
            )
            ->where('a.kota_id', '=', $id)
            ->first();
        if (isset($tarif)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Tarif',
            ];
            goto selesai;
        }
        
        $suratpengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.dari_id',
                'a.sampai_id'
            )
            ->where('a.dari_id', '=', $id)
            ->where('a.sampai_id', '=', $id)
            ->first();
        if (isset($suratpengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];
            goto selesai;
        }

        $upahSupir = DB::table('upahsupir')
            ->from(
                DB::raw("upahsupir as a with (readuncommitted)")
            )
            ->select(
                'a.kotadari_id',
                'a.kotasampai_id',
            )
            ->where('a.kotadari_id', '=', $id)
            ->where('a.kotasampai_id', '=', $id)
            ->first();
        if (isset($upahSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Supir',
            ];
            goto selesai;
        }

        $upahRitasi = DB::table('upahritasi')
            ->from(
                DB::raw("upahritasi as a with (readuncommitted)")
            )
            ->select(
                'a.kotadari_id',
                'a.kotasampai_id'
            )
            ->where('a.kotadari_id', '=', $id)
            ->where('a.kotasampai_id', '=', $id)
            ->first();
        if (isset($upahRitasi)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Ritasi',
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

        $aktif = request()->aktif ?? '';

        $query = Kota::from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'kota.id',
                'kota.kodekota',
                'kota.keterangan',
                'zona.zona as zona_id',
                'parameter.memo as statusaktif',
                'kota.modifiedby',
                'kota.created_at',
                'kota.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kota.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'kota.zona_id', '=', 'zona.id');





        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('kota.statusaktif', '=', $statusaktif->id);
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
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'memo',
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id]);
        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function findAll($id)
    {

        $query = Kota::from(DB::raw("kota with (readuncommitted)"))
            ->select(DB::raw('kota.*, zona.zona as zona'))
            ->join(DB::raw("zona with (readuncommitted)"), 'kota.zona_id', 'zona.id')->whereRaw("kota.id = $id");

        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            DB::raw(
                "$this->table.id,
            $this->table.kodekota,
            $this->table.keterangan,
            'zona.zona',
            'parameter.text as statusaktif',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )

            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kota.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'kota.zona_id', '=', 'zona.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodekota', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('zona', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodekota', 'keterangan', 'zona', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.zona', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where('kota.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->orWhereRaw("(kota.id like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(kota.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%')");
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->orWhere('zona.zona', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere('kota.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
