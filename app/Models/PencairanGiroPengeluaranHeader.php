<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PencairanGiroPengeluaranHeader extends MyModel
{
    use HasFactory;

    protected $table = 'PencairanGiroPengeluaranHeader';
    protected $anotherTable = 'pengeluaranheader';
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
        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode,0,2);
        $year = substr($periode,3);
        
        $query = DB::table($this->anotherTable)->select(DB::raw("pengeluaranheader.nobukti as pengeluaran_nobukti,pengeluaranheader.id, pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac, pengeluaranheader.modifiedby, pengeluaranheader.created_at,pengeluaranheader.updated_at,pengeluaranheader.keterangan, alatbayar.keterangan as alatbayar_id, pgp.nobukti, pgp.tglbukti, parameter.memo as statusapproval, (SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluarandetail.alatbayar_id=2) as nominal")
        )
        ->distinct('pengeluaranheader.nobukti')
        ->leftJoin('pengeluarandetail','pengeluarandetail.nobukti','pengeluaranheader.nobukti')
        ->leftJoin('pencairangiropengeluaranheader as pgp','pgp.pengeluaran_nobukti','pengeluaranheader.nobukti')
        ->leftJoin('parameter','pgp.statusapproval','parameter.id')
        ->leftJoin('bank','pengeluaranheader.bank_id','bank.id')
        ->leftJoin('alatbayar','pengeluarandetail.alatbayar_id','alatbayar.id')
        ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
        ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
        ->where('pengeluarandetail.alatbayar_id','2');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query,'pengeluaranheader');
        $this->filter($query,'pengeluaranheader');
        $this->paginate($query);

        $data = $query->get();

        return $data;

    }

    
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            $this->table.keterangan,
            $this->table.pengeluaran_nobukti,
            statusapproval.text as statusapproval,
            $this->table.userapproval,
            $this->table.tglapproval,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
            ->leftJoin('parameter as statusapproval', 'pencairangiropengeluaranheader.statusapproval', 'statusapproval.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti', 1000)->default('1900/1/1');
            $table->string('keterangan', 1000)->default('');
            $table->string('pengeluaran_nobukti', 1000)->default('');
            $table->string('statusapproval', 1000)->default('');
            $table->string('userapproval', 1000)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query,'pencairangiropengeluaranheader');
        $models = $this->filter($query,'pencairangiropengeluaranheader');
        DB::table($temp)->insertUsing([
            'id', 'nobukti', 'tglbukti', 'keterangan', 'pengeluaran_nobukti', 'statusapproval', 'userapproval', 'tglapproval', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


        return  $temp;
    }


    public function sort($query,$table)
    {
        return $query->orderBy($table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query,$table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'alatbayar_id') {
                            $query = $query->where('alatbayar.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nobukti') {
                            $query = $query->where('pgp.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->where('pgp.tglbukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'pengeluaran_nobukti') {
                            $query = $query->where('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                        }else if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        }else if ($filters['field'] == 'alatbayar_id') {
                            $query = $query->orWhere('alatbayar.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nobukti') {
                            $query = $query->orWhere('pgp.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->orWhere('pgp.tglbukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'pengeluaran_nobukti') {
                            $query = $query->orWhere('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
