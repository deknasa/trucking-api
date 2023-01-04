<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranStokHeader extends MyModel
{
    use HasFactory;

    protected $table = 'PengeluaranStokHeader';

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
        
        ->leftJoin('gudang','pengeluaranstokheader.gudang_id','gudang.id')
        ->leftJoin('pengeluaranstok','pengeluaranstokheader.pengeluaranstok_id','pengeluaranstok.id')
        ->leftJoin('trado','pengeluaranstokheader.trado_id','trado.id')
        ->leftJoin('supplier','pengeluaranstokheader.supplier_id','supplier.id')
        ->leftJoin('kerusakan','pengeluaranstokheader.kerusakan_id','kerusakan.id')
        ->leftJoin('penerimaanstokheader as penerimaan' ,'pengeluaranstokheader.penerimaanstok_nobukti','penerimaan.nobukti')
        ->leftJoin('pengeluaranstokheader as pengeluaran' ,'pengeluaranstokheader.pengeluaranstok_nobukti','pengeluaran.nobukti')
        // ->leftJoin('servicein','pengeluaranstokheader.servicein_nobukti','servicein.nobukti')
        ->leftJoin('supir','pengeluaranstokheader.supir_id','supir.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
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
                            case 'pengeluaranstok':
                                $query = $query->where('pengeluaranstok.kodepengeluaran', 'LIKE', "%$filters[data]%");
                                break;
                            case 'gudang':
                                $query = $query->where('gudang.gudang', 'LIKE', "%$filters[data]%");
                                break;
                            case 'trado':
                                $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                                break;
                            case 'supplier':
                                $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                                break;
                            case 'kerusakan':
                                $query = $query->where('kerusakan.keteragan', 'LIKE', "%$filters[data]%");
                                break;
                            case 'supir':
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
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
                            case 'pengeluaranstok':
                                $query = $query->orWhere('pengeluaranstok.kodepengeluaran', 'LIKE', "%$filters[data]%");
                                break;
                            case 'gudang':
                                $query = $query->orWhere('gudang.gudang', 'LIKE', "%$filters[data]%");
                                break;
                            case 'trado':
                                $query = $query->orWhere('trado.keterangan', 'LIKE', "%$filters[data]%");
                                break;
                            case 'supplier':
                                $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                                break;
                            case 'kerusakan':
                                $query = $query->orWhere('kerusakan.keterangan', 'LIKE', "%$filters[data]%");
                                break;
                            case 'supir':
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
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

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti',50)->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('pengeluaranstok_id')->default(0);            
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->unsignedBigInteger('gudang_id')->default('0');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->unsignedBigInteger('supplier_id')->default('0');
            $table->string('pengeluaranstok_nobukti',50)->default('');
            $table->string('penerimaanstok_nobukti',50)->default('');
            $table->string('servicein_nobukti',50)->default('');
            $table->unsignedBigInteger('kerusakan_id')->default('0');
            $table->unsignedBigInteger('statusformat')->default(0);  
            $table->string('modifiedby',50)->default('');
            $table->increments('position');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
        });

        $query = DB::table($modelTable);
        $query = $this->select(
            "id",
            "nobukti",
            "tglbukti",
            "keterangan",
            "pengeluaranstok_id",
            "trado_id",
            "gudang_id",
            "supir_id",
            "supplier_id",
            "pengeluaranstok_nobukti",
            "penerimaanstok_nobukti",
            "servicein_nobukti",
            "kerusakan_id",
            "statusformat",
            "modifiedby",
        );
        $query = $this->sort($query);
        $models = $this->filter($query);
        
        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "tglbukti",
            "keterangan",
            "pengeluaranstok_id",
            "trado_id",
            "gudang_id",
            "supir_id",
            "supplier_id",
            "pengeluaranstok_nobukti",
            "penerimaanstok_nobukti",
            "servicein_nobukti",
            "kerusakan_id",
            "statusformat",
            "modifiedby",
        ], $models);

        return  $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.keterangan",
            "$this->table.pengeluaranstok_id",
            "$this->table.trado_id",
            "$this->table.gudang_id",
            "$this->table.supir_id",
            "$this->table.supplier_id",
            "$this->table.pengeluaranstok_nobukti",
            "$this->table.penerimaanstok_nobukti",
            "$this->table.servicein_nobukti",
            "$this->table.kerusakan_id",
            "$this->table.statuscetak",
            "$this->table.statusformat",
            "$this->table.modifiedby",
            "kerusakan.keterangan as kerusakan",
            "pengeluaranstok.kodepengeluaran as pengeluaranstok",
            "trado.keterangan as trado",
            "gudang.gudang as gudang",
            "supir.namasupir as supir",
            "supplier.namasupplier as supplier",
        );
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
        ->leftJoin('gudang','pengeluaranstokheader.gudang_id','gudang.id')
        ->leftJoin('pengeluaranstok','pengeluaranstokheader.pengeluaranstok_id','pengeluaranstok.id')
        ->leftJoin('trado','pengeluaranstokheader.trado_id','trado.id')
        ->leftJoin('supplier','pengeluaranstokheader.supplier_id','supplier.id')
        ->leftJoin('kerusakan','pengeluaranstokheader.kerusakan_id','kerusakan.id')
        ->leftJoin('penerimaanstokheader as penerimaan' ,'pengeluaranstokheader.penerimaanstok_nobukti','penerimaan.nobukti')
        ->leftJoin('pengeluaranstokheader as pengeluaran' ,'pengeluaranstokheader.pengeluaranstok_nobukti','pengeluaran.nobukti')
        // ->leftJoin('servicein','pengeluaranstokheader.servicein_nobukti','servicein.nobukti')
        ->leftJoin('supir','pengeluaranstokheader.supir_id','supir.id');

        $data = $query->where("$this->table.id",$id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
 
 
 