<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class SubKelompok extends MyModel
{
    use HasFactory;

    protected $table = 'subkelompok';

    protected $casts = [
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

        $query = DB::table($this->table)->select(
            'subkelompok.id',
            'subkelompok.kodesubkelompok',
            'subkelompok.keterangan',
            'kelompok.keterangan as kelompok_id',
            'parameter.memo as statusaktif',
            'subkelompok.modifiedby',
            'subkelompok.created_at',
            'subkelompok.updated_at'
        )
            ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
            ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
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
    
    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.kodesubkelompok,
            $this->table.keterangan,

            'kelompok.keterangan as kelompok_id',
            'parameter.text as statusaktif',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
            ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('kodesubkelompok',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('kelompok_id')->default('');
            $table->string('statusaktif', 500)->default('');


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
        DB::table($temp)->insertUsing(['id', 'kodesubkelompok', 'keterangan', 'kelompok_id', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
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
