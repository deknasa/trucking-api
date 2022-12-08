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

    public function get()
    {
        $this->setRequestParameters();
        $query = DB::table('parameter')
            ->select('parameter.id', 
            'parameter.grp', 
            'parameter.subgrp', 
            'parameter.kelompok', 
            'parameter.text', 
            'parameter.modifiedby', 
            'parameter.created_at', 
            'parameter.updated_at', 
            DB::raw("case when parameter.type = 0 then '' else B.grp end as type"),
            )
            ->leftJoin('parameter as B', 'parameter.type', 'B.id');

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
        $query = DB::table('parameter as A')
            ->select('A.id', 'A.grp', 'A.subgrp', 'A.kelompok', 'A.text', 'A.memo', 'A.type', 'B.grp as grup')
            ->leftJoin('parameter as B', 'A.type', 'B.id')
            ->where('A.id', $id);

        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.grp",
            "$this->table.subgrp",
            "$this->table.text",
            "$this->table.memo",
            "$this->table.kelompok",
            DB::raw("case when parameter.type = 0 then '' else B.grp end as type"),
            "$this->table.singkatan",
            "$this->table.warna",
            "$this->table.created_at",
            "$this->table.updated_at",
            "$this->table.modifiedby"
        )->leftJoin('parameter as B', 'parameter.type', 'B.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, 10000);

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('grp', 500)->default('');
            $table->string('subgrp', 250)->default('');
            $table->string('text', 500)->default('');
            $table->string('memo', 1000)->default('');
            $table->string('kelompok', 1000)->default('');
            $table->string('type', 1000)->default('');
            $table->string('singkatan', 1000)->default('');
            $table->string('warna', 1000)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->string('modifiedby', 50)->default('');
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
            'type',
            'singkatan',
            'warna',
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
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'like', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'type') {
                            $query = $query->orWhere('B.grp', 'LIKE', "%$filters[data]%");
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
