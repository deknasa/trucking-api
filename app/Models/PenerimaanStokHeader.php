<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenerimaanStokHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaanstokheader';

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

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
        ->leftJoin('gudang as gudangs','penerimaanstokheader.gudang_id','gudangs.id')
        ->leftJoin('gudang as dari','penerimaanstokheader.gudangdari_id','dari.id')
        ->leftJoin('gudang as ke','penerimaanstokheader.gudangke_id','ke.id')
        
        ->leftJoin('penerimaanstok','penerimaanstokheader.penerimaanstok_id','penerimaanstok.id')
        ->leftJoin('trado','penerimaanstokheader.trado_id','trado.id')
        ->leftJoin('supplier','penerimaanstokheader.supplier_id','supplier.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    
    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "penerimaanstok.kodepenerimaan as penerimaanstok",
            "$this->table.penerimaanstok_nobukti",
            "$this->table.pengeluaranstok_nobukti",
            "gudangs.gudang as gudang",
            "trado.keterangan as trado",
            "supplier.namasupplier as supplier",
            "$this->table.nobon",
            "$this->table.hutang_nobukti",
            "dari.gudang as gudangdari",
            "ke.gudang as gudangke",
            "$this->table.coa",
            "$this->table.keterangan",
            "$this->table.modifiedby",
        );
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, 10000);

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');            
            $table->unsignedBigInteger('penerimaanstok_id')->default(0);
            $table->string('penerimaanstok_nobukti',50)->default('');
            $table->string('pengeluaranstok_nobukti',50)->default('');
            $table->unsignedBigInteger('supplier_id')->default(0);            
            $table->string('nobon', 50)->default('');
            $table->string('hutang_nobukti', 50)->default('');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->unsignedBigInteger('gudang_id')->default('0');
            $table->unsignedBigInteger('gudangdari_id')->default('0');
            $table->unsignedBigInteger('gudangke_id')->default('0');            
            $table->string('coa',50)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('statusformat')->default(0);   
            $table->string('modifiedby',50)->default('');
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
                        switch ($filters['field']) {
                            case 'penerimaanstok':
                                $query = $query->where('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                                break;
                            case 'gudang':
                                $query = $query->where('gudangs.gudang', 'LIKE', "%$filters[data]%");
                                break;
                            case 'trado':
                                $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                                break;
                            case 'supplier':
                                $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                                break;
                            case 'gudangdari':
                                $query = $query->where('dari.gudang', 'LIKE', "%$filters[data]%");
                                break;
                            case 'gudangke':
                                $query = $query->where('ke.gudang', 'LIKE', "%$filters[data]%");
                                break;
                            
                            default:
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                break;
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        switch ($filters['field']) {
                            case 'penerimaanstok':
                                $query = $query->where('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                                break;
                            case 'gudang':
                                $query = $query->orWhere('gudangs.gudang', 'LIKE', "%$filters[data]%");
                                break;
                            case 'trado':
                                $query = $query->orWhere('trado.keterangan', 'LIKE', "%$filters[data]%");
                                break;
                            case 'supplier':
                                $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                                break;
                            case 'gudangdari':
                                $query = $query->orWhere('dari.gudang', 'LIKE', "%$filters[data]%");
                                break;
                            case 'gudangke':
                                $query = $query->orWhere('ke.gudang', 'LIKE', "%$filters[data]%");
                                break;
                            
                            default:
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                break;
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

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
        ->leftJoin('gudang as gudangs','penerimaanstokheader.gudang_id','gudangs.id')
        ->leftJoin('gudang as dari','penerimaanstokheader.gudangdari_id','dari.id')
        ->leftJoin('gudang as ke','penerimaanstokheader.gudangke_id','ke.id')
        
        ->leftJoin('penerimaanstok','penerimaanstokheader.penerimaanstok_id','penerimaanstok.id')
        ->leftJoin('trado','penerimaanstokheader.trado_id','trado.id')
        ->leftJoin('supplier','penerimaanstokheader.supplier_id','supplier.id');
        $data = $query->where("$this->table.id",$id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
