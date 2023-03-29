<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RekapPenerimaanHeader extends MyModel
{
    use HasFactory;

    protected $table = 'rekappenerimaanheader';

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
        ->leftJoin('parameter as statusapproval','rekappenerimaanheader.statusapproval','statusapproval.id')
        ->leftJoin('parameter as statuscetak','rekappenerimaanheader.statuscetak','statuscetak.id')
        ->leftJoin('bank','rekappenerimaanheader.bank_id','bank.id');
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
            $query->where('rekappenerimaanheader.statuscetak','<>', request()->cetak)
                  ->whereYear('rekappenerimaanheader.tglbukti','=', request()->year)
                  ->whereMonth('rekappenerimaanheader.tglbukti','=', request()->month);
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
            $table->integer('statusapproval')->length(11)->nullable();
            $table->integer('statuscetak')->length(11)->nullable();
            $table->string('userapproval',50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->select(
            "id",
            "nobukti",
            "tglbukti",
            "bank_id",
            "tgltransaksi",
            "statusapproval",
            "statuscetak",
            "userapproval",
            "tglapproval",
            "modifiedby",
            "created_at",
            "updated_at",
        );
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d',strtotime(request()->tgldari )), date('Y-m-d',strtotime(request()->tglsampai ))]);
        }
        $this->sort($query);
        $models = $this->filter($query);
        
        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "tglbukti",
            "bank_id",
            "tgltransaksi",
            "statusapproval",
            "statuscetak",
            "userapproval",
            "tglapproval",
            "modifiedby",
            "created_at",
            "updated_at",
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
            "$this->table.userapproval",
            
            DB::raw("(case when year(isnull($this->table.tglapproval,'1900/1/1'))=1900 then null else $this->table.tglapproval end) as tglapproval"),
            "$this->table.statusformat",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
            "bank.namabank as bank",
            "statusapproval.memo as  statusapproval",
            "statuscetak.memo as  statuscetak",

        );
    }
    public function getRekapPenerimaanHeader($id)
    {
        $this->setRequestParameters();

        $query = DB::table('rekappenerimaandetail')->select(
            "rekappenerimaandetail.nobukti",
            "rekappenerimaandetail.penerimaan_nobukti",
            "rekappenerimaandetail.keterangan as keterangan_detail",
            "rekappenerimaandetail.tgltransaksi as tglbukti",
            "rekappenerimaandetail.nominal"
            )
        ->where('rekappenerimaan_id',$id);
        $data = $query->get();
            
        return $data;
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
        ->leftJoin('parameter as statusapproval','rekappenerimaanheader.statusapproval','statusapproval.id')
        ->leftJoin('parameter as statuscetak','rekappenerimaanheader.statuscetak','statuscetak.id')
        ->leftJoin('bank','rekappenerimaanheader.bank_id','bank.id');

        $data = $query->where("$this->table.id",$id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


}
