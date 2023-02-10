<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotaKreditHeader extends MyModel
{
    use HasFactory;

    protected $table = 'notakreditheader';

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
        ->leftJoin('parameter as statuscetak','notakreditheader.statuscetak','statuscetak.id')
        ->leftJoin('pelunasanpiutangheader as pelunasanpiutang','notakreditheader.pelunasanpiutang_nobukti','pelunasanpiutang.nobukti')
        ->leftJoin('parameter','notakreditheader.statusapproval','parameter.id');


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

   
    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti',50)->unique();
            $table->string('pelunasanpiutang_nobukti',50)->default('');
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('postingdari',50)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->date('tgllunas')->default('1900/1/1');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
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
            "pelunasanpiutang_nobukti",
            "tglbukti",
            "postingdari",
            "statusapproval",
            "tgllunas",
            "userapproval",
            "tglapproval",
            "statusformat",
            "modifiedby",
        );
        $query = $this->sort($query);
        $models = $this->filter($query);
        
        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "pelunasanpiutang_nobukti",
            "tglbukti",
            "postingdari",
            "statusapproval",
            "tgllunas",
            "userapproval",
            "tglapproval",
            "statusformat",
            "modifiedby",
        ], $models);
        return $temp;
    }
        
    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.pelunasanpiutang_nobukti",
            "$this->table.tglbukti",
            DB::raw('(case when (year(notakreditheader.tglapproval) <= 2000) then null else notakreditheader.tglapproval end ) as tglapproval'),
            "$this->table.postingdari",
            "$this->table.statusapproval",
            "$this->table.tgllunas",
            "$this->table.userapproval",
            "$this->table.statusformat",
            "$this->table.modifiedby",
            "$this->table.statuscetak",
            "$this->table.created_at",
            "$this->table.updated_at",
            "parameter.memo as  statusapproval_memo",
            "statuscetak.memo as  statuscetak_memo",
        );
    }

    public function getNotaKredit($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')
        ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        notakreditheader.keterangan,
        pelunasanpiutangdetail.coapenyesuaian,
        COALESCE (pelunasanpiutangdetail.penyesuaian, 0) as penyesuaian '))

        ->leftJoin('piutangheader','piutangheader.nobukti','pelunasanpiutangdetail.piutang_nobukti')
        ->leftJoin('notakreditheader','notakreditheader.pelunasanpiutang_nobukti','pelunasanpiutangdetail.nobukti')
        ->leftJoin('pelanggan', 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
        ->leftJoin('agen', 'pelunasanpiutangdetail.agen_id', 'agen.id')
        ->whereRaw(" EXISTS (
            SELECT notakreditheader.pelunasanpiutang_nobukti
            FROM notakreditdetail
			left join notakreditheader on notakreditdetail.notakredit_id = notakreditheader.id
            WHERE notakreditheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
        ->where('pelunasanpiutangdetail.penyesuaian', '>', 0)
        ->where('notakreditheader.id' , $id);
            
       

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
                            case 'statusapproval_memo':
                                $query = $query->where('parameter.memo', 'LIKE', "%$filters[data]%");
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
                            case 'statusapproval_memo':
                                $query = $query->where('parameter.memo', 'LIKE', "%$filters[data]%");
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
        if (request()->cetak && request()->periode) {
            $query->where('notakreditheader.statuscetak','<>', request()->cetak)
                  ->whereYear('notakreditheader.tglbukti','=', request()->year)
                  ->whereMonth('notakreditheader.tglbukti','=', request()->month);
            return $query;
        }

        return $query;
    }
    public function findAll($id)
    {
        $this->setRequestParameters();
        $query = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"));
        $query = $this->selectColumns($query)
        ->leftJoin('parameter','notakreditheader.statusapproval','parameter.id')
        ->leftJoin('parameter as statuscetak','notakreditheader.statuscetak','statuscetak.id')
        ->leftJoin('pelunasanpiutangheader as pelunasanpiutang','notakreditheader.pelunasanpiutang_nobukti','pelunasanpiutang.nobukti');
 
        $data = $query->where("$this->table.id",$id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
