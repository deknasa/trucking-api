<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Ritasi extends MyModel
{
    use HasFactory;

    protected $table = 'ritasi';

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

        $query = DB::table($this->table)
        ->select(
            'ritasi.id',
            'ritasi.nobukti',
            'ritasi.tglbukti',
            'parameter.text as statusritasi',
            'suratpengantar.nobukti as suratpengantar_nobukti',
            'supir.namasupir as supir_id',
            'trado.keterangan as trado_id',
            'ritasi.jarak',
            'ritasi.gaji',
            'dari.keterangan as dari_id',
            'sampai.keterangan as sampai_id',
            'ritasi.modifiedby',
            'ritasi.created_at',
            'ritasi.updated_at'
        )
        ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
        ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
        ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
        ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
        ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
        ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id');

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
            $table->unsignedBigInteger('statusritasi')->default(0);
        });

        $statusritasi=Parameter::from (
            db::Raw("parameter with (readuncommitted)")
        )
        ->select (
            'id'
        )
        ->where('grp','=','STATUS RITASI')
        ->where('subgrp','=','STATUS RITASI')
        ->where('default', '=', 'YA')
        ->first();
        DB::table($tempdefault)->insert(["statusritasi" => $statusritasi->id]);
        
        $query=DB::table($tempdefault)->from(
            DB::raw($tempdefault )
        )
            ->select(
                'statusritasi');

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function find($id)
    {
        $query = DB::table('ritasi')->select(
            'ritasi.id',
            'ritasi.nobukti',
            'ritasi.tglbukti',
            'ritasi.statusritasi',
            'ritasi.suratpengantar_nobukti',
            'ritasi.dari_id',
            'dari.keterangan as dari',
            'ritasi.sampai_id',
            'sampai.keterangan as sampai',
            'ritasi.trado_id',
            'trado.keterangan as trado',
            'ritasi.supir_id',
            'supir.namasupir as supir'
        )
        ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
        ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
        ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
        ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id')
        ->where('ritasi.id',$id);

        $data = $query->first();
        return $data;
    }
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
            "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'parameter.text as statusritasi',
            'suratpengantar.nobukti as suratpengantar_nobukti',
            'supir.namasupir as supir_id',
            'trado.keterangan as trado_id',
            $this->table.jarak,
            $this->table.gaji,
            'dari.keterangan as dari_id',
            'sampai.keterangan as sampai_id',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
        ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
        ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
        ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
        ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
        ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
        ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('statusritasi', 1000)->default('');
            $table->string('suratpengantar_nobukti', 1000)->default('');
            $table->string('supir_id', 1000)->default('');
            $table->string('trado_id', 1000)->default('');
            $table->string('jarak', 1000)->default('');
            $table->string('gaji', 1000)->default('');
            $table->string('dari_id', 1000)->default('');
            $table->string('sampai_id', 1000)->default('');
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
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','statusritasi','suratpengantar_nobukti','supir_id','trado_id','jarak','gaji','dari_id','sampai_id','modifiedby','created_at','updated_at'],$models);


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
                        if ($filters['field'] == 'statusritasi') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'dari_id') {
                            $query = $query->where('dari.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'sampai_id') {
                            $query = $query->where('sampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusritasi') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'dari_id') {
                            $query = $query->where('dari.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'sampai_id') {
                            $query = $query->where('sampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
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
