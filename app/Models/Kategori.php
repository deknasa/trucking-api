<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Kategori extends MyModel
{
    use HasFactory;

    protected $table = 'kategori';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';

        $query = Kategori::from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'kategori.id',
                'kategori.kodekategori',
                'kategori.keterangan',
                'parameter.memo as statusaktif',
                'p.keterangan as subkelompok',
                'kategori.modifiedby',
                'kategori.created_at',
                'kategori.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kategori.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("subkelompok AS p with (readuncommitted)"), 'kategori.subkelompok_id', '=', 'p.id');
           

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('kategori.statusaktif', '=', $statusaktif->id);
        }
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function default()
    {
        
        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->default(0);
        });

        $statusaktif=Parameter::from (
            db::Raw("parameter with (readuncommitted)")
        )
        ->select (
            'id'
        )
        ->where('grp','=','STATUS AKTIF')
        ->where('subgrp','=','STATUS AKTIF')
        ->where('default', '=', 'YA')
        ->first();
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id]);
        
        $query=DB::table($tempdefault)->from(
            DB::raw($tempdefault )
        )
            ->select(
                'statusaktif');

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function find($id)
    {
        $this->setRequestParameters();

        $data = Kategori::from(DB::raw("kategori with (readuncommitted)"))
            ->select(
                'kategori.id',
                'kategori.kodekategori',
                'kategori.keterangan',
                'kategori.subkelompok_id',
                'p.keterangan as subkelompok',
                'kategori.statusaktif',
                'kategori.modifiedby',
                'kategori.created_at',
                'kategori.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kategori.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("subkelompok AS p with (readuncommitted)"), 'kategori.subkelompok_id', '=', 'p.id')
            ->where('kategori.id', $id)->first();



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
            $this->table.kodekategori,
            $this->table.keterangan,
            'parameter.text as statusaktif',
            'p.keterangan as subkelompok',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kategori.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("subkelompok AS p with (readuncommitted)"), 'kategori.subkelompok_id', '=', 'p.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('kodekategori', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('statusaktif', 1000)->default('');
            $table->string('subkelompok', 1000)->default('');
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
        DB::table($temp)->insertUsing(['id', 'kodekategori', 'keterangan', 'statusaktif', 'subkelompok', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'subkelompok') {
                            $query = $query->where('p.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where('kategori.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->orWhereRaw("(kategori.id like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(kategori.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%')");
                        } elseif ($filters['field'] == 'subkelompok') {
                            $query = $query->orWhere('p.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere('kategori.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
