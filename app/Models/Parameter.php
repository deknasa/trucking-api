<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Parameter extends MyModel
{
    use HasFactory;

    protected $table = 'parameter';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getdefaultparameter($data) {
        $grp=$data['grp'] ?? '';
        $subgrp=$data['subgrp'] ?? '';


        $query=DB::table('parameter')
            ->from (
                DB::raw("parameter with (readuncommitted)")
            )
            ->select (
                'id'
            )
            ->Where('grp','=',$grp)
            ->Where('subgrp','=',$subgrp)
            ->Where('default','=','YA')
            ->first();

            if (isset( $query)) {
                $data= $query->id;
            }  else  {
                $data=0;
            }
            
            return $data;

    }

    public function get()
    {
        $this->setRequestParameters();
        $query = DB::table('parameter')->from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->select(
                'parameter.id',
                'parameter.grp',
                'parameter.subgrp',
                'parameter.kelompok',
                'parameter.text',
                'parameter.memo',
                'parameter.default',
                'parameter.modifiedby',
                'parameter.created_at',
                'parameter.updated_at',
                DB::raw("case when parameter.type = 0 then '' else B.grp end as type"),
            )
            ->leftJoin(DB::raw("parameter as B with (readuncommitted)"), 'parameter.type', 'B.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getcoa($filter)
    {
        $getcoa = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->select('memo')->where('kelompok', $filter)->get();
        $jurnal = [];
        foreach ($getcoa as $key => $coa) {
            $a = 0;
            $memo = json_decode($coa->memo, true);
            
            $ketcoa = AkunPusat::from(DB::raw("akunpusat with (readuncommitted)"))
            ->select('keterangancoa')->where('coa', $memo['JURNAL'])->first();
            $jurnal[] = [
                'coa' => $memo['JURNAL'],
                'keterangancoa' => $ketcoa->keterangancoa
            ];
        }
         
        return $jurnal;
    }
    public function findAll($id)
    {
        $query = DB::table('parameter as A')->from(
            DB::raw("parameter as A with (readuncommitted)")
        )
            ->select('A.id', 'A.grp', 'A.subgrp', 'A.kelompok', 'A.text', 'A.memo', 'A.default', 'A.type', 'B.grp as grup')
            ->leftJoin(DB::raw("parameter as B with (readuncommitted)"), 'A.type', 'B.id')
            ->where('A.id', $id);

        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.grp",
                "$this->table.subgrp",
                "$this->table.text",
                "$this->table.memo",
                "$this->table.kelompok",
                "$this->table.default",
                DB::raw("case when parameter.type = 0 then '' else B.grp end as type"),
                "$this->table.created_at",
                "$this->table.updated_at",
                "$this->table.modifiedby"
            )->leftJoin(DB::raw("parameter as B with (readuncommitted)"), 'parameter.type', 'B.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('grp', 500)->nullable();
            $table->string('subgrp', 250)->nullable();
            $table->string('text', 500)->nullable();
            $table->longText('memo')->nullable();
            $table->string('kelompok', 1000)->nullable();
            $table->string('default', 1000)->nullable();
            $table->string('type', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'grp',
            'subgrp',
            'text',
            'memo',
            'kelompok',
            'default',
            'type',
            'created_at',
            'updated_at',
            'modifiedby'
        ], $models);

        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'grp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.subgrp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        if ($this->params['sortIndex'] == 'subgrp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.grp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'type') {
                            $query = $query->where('B.grp', 'like', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'like', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'type') {
                            $query = $query->orWhere('B.grp', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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
