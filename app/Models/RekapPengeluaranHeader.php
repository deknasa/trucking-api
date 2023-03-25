<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RekapPengeluaranHeader extends MyModel
{
    use HasFactory;

    protected $table = 'rekappengeluaranheader';

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
        ->leftJoin('parameter as statusapproval','rekappengeluaranheader.statusapproval','statusapproval.id')
        ->leftJoin('parameter as statuscetak','rekappengeluaranheader.statuscetak','statuscetak.id')

        ->leftJoin('bank','rekappengeluaranheader.bank_id','bank.id');
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d',strtotime(request()->tgldari )), date('Y-m-d',strtotime(request()->tglsampai ))]);
        }
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
                            case 'bank':
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                                break;
                            default:
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                break;
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            switch ($filters['field']) {
                                case 'bank':
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                    break;
                                default:
                                    $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    break;
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
        if (request()->cetak && request()->periode) {
            $query->where('rekappengeluaranheader.statuscetak','<>', request()->cetak)
                  ->whereYear('rekappengeluaranheader.tglbukti','=', request()->year)
                  ->whereMonth('rekappengeluaranheader.tglbukti','=', request()->month);
            return $query;
        }
        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->date('tgltransaksi')->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval',50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();  
            $table->string('modifiedby',50)->nullable();
            $table->increments('position');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $query = DB::table($modelTable);
        $query = $this->select(
            "id",
            "nobukti",
            "tglbukti",
            "bank_id",
            "tgltransaksi",
            "keterangan",
            "statusapproval",
            "userapproval",
            "tglapproval",
            "statusformat",
            "modifiedby",
        );
        $query = $this->sort($query);
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d',strtotime(request()->tgldari )), date('Y-m-d',strtotime(request()->tglsampai ))]);
        }
        $models = $this->filter($query);
        
        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "tglbukti",
            "bank_id",
            "tgltransaksi",
            "keterangan",
            "statusapproval",
            "userapproval",
            "tglapproval",
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
            "$this->table.bank_id",
            "$this->table.tgltransaksi",
            "$this->table.keterangan",
            "$this->table.statusapproval",
            "$this->table.userapproval",
            DB::raw('(case when (year(rekappengeluaranheader.tglapproval) <= 2000) then null else rekappengeluaranheader.tglapproval end ) as tglapproval'),
            "$this->table.statusformat",
            "$this->table.modifiedby",
            "bank.namabank as bank",
            "statusapproval.memo as  statusapproval",
            "statuscetak.memo as  statuscetak",
            "$this->table.created_at",
            "$this->table.updated_at",

        );
    }
    public function getRekapPengeluaranHeader($id)
    {
        $this->setRequestParameters();

        $query = DB::table('rekappengeluarandetail')->select(
            "rekappengeluarandetail.nobukti",
            "rekappengeluarandetail.pengeluaran_nobukti",
            "rekappengeluarandetail.keterangan as keterangan_detail",
            "rekappengeluarandetail.tgltransaksi as tglbukti",
            "rekappengeluarandetail.nominal"
            )
        ->where('rekappengeluaran_id',$id);
        $data = $query->get();
            
        return $data;
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
        ->leftJoin('parameter as statusapproval','rekappengeluaranheader.statusapproval','statusapproval.id')
        ->leftJoin('parameter as statuscetak','rekappengeluaranheader.statuscetak','statuscetak.id')
        ->leftJoin('bank','rekappengeluaranheader.bank_id','bank.id');

        $data = $query->where("$this->table.id",$id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

}
