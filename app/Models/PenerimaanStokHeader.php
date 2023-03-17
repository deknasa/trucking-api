<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // dd(request());

        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $po = Parameter::where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
        $rtb = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
        ->whereBetween('tglbukti', [date('Y-m-d',strtotime(request()->tgldari)), date('Y-m-d',strtotime(request()->tglsampai))])
        ->leftJoin('gudang as gudangs','penerimaanstokheader.gudang_id','gudangs.id')
        ->leftJoin('gudang as dari','penerimaanstokheader.gudangdari_id','dari.id')
        ->leftJoin('gudang as ke','penerimaanstokheader.gudangke_id','ke.id')
        ->leftJoin('parameter as statuscetak','penerimaanstokheader.statuscetak','statuscetak.id')
        ->leftJoin('penerimaanstok','penerimaanstokheader.penerimaanstok_id','penerimaanstok.id')
        ->leftJoin('trado','penerimaanstokheader.trado_id','trado.id')
        ->leftJoin('trado as tradodari ','penerimaanstokheader.tradodari_id','tradodari.id')
        ->leftJoin('trado as tradoke ','penerimaanstokheader.tradoke_id','tradoke.id')
        ->leftJoin('gandengan as gandengandari ','penerimaanstokheader.gandengandari_id','gandengandari.id')
        ->leftJoin('gandengan as gandenganke ','penerimaanstokheader.gandenganke_id','gandenganke.id')
        ->leftJoin('gandengan as gandengan ','penerimaanstokheader.gandenganke_id','gandengan.id')
        ->leftJoin('supplier','penerimaanstokheader.supplier_id','supplier.id');
        if (request()->penerimaanstok_id==$spb->text) {
            $query->leftJoin('penerimaanstokheader as pobeli','penerimaanstokheader.penerimaanstok_nobukti','pobeli.nobukti');
            $query->where('penerimaanstokheader.penerimaanstok_id','=',$po->text);
            $query->whereRaw("isnull(pobeli.nobukti,'')=''");
            // dd($query->get());
        }

        if (request()->supplier_id) {
            // $query->leftJoin('penerimaanstokheader as pobeli','penerimaanstokheader.penerimaanstok_nobukti','pobeli.nobukti');
            $query->where('penerimaanstokheader.supplier_id','=',request()->supplier_id);
            // $query->whereRaw("isnull(pobeli.nobukti,'')=''");
            // dd($query->get());
        }
        if (request()->pengeluaranstok_id == $rtb->text) {
            //jika retur cari penerimaan hanya
            $query->where('penerimaanstokheader.penerimaanstok_id','=',$spb->text);
        }

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
            "trado.kodetrado as trado",
            "tradodari.keterangan as tradodari",
            "tradoke.keterangan as tradoke",
            "gandengandari.keterangan as gandengandari",
            "gandenganke.keterangan as gandenganke",
            "supplier.namasupplier as supplier",
            "$this->table.nobon",
            "$this->table.hutang_nobukti",
            "dari.gudang as gudangdari",
            "ke.gudang as gudangke",
            "$this->table.statusformat",
            "$this->table.coa",
            "$this->table.keterangan",
            "$this->table.modifiedby",
            "penerimaanstokheader.gudang_id",
            "penerimaanstokheader.gudangdari_id",
            "penerimaanstokheader.gudangke_id",
            "penerimaanstokheader.penerimaanstok_id",
            "penerimaanstokheader.trado_id",
            "penerimaanstokheader.tradoke_id",
            "penerimaanstokheader.tradodari_id",
            "penerimaanstokheader.gandenganke_id",
            "penerimaanstokheader.gandengandari_id",
            "penerimaanstokheader.gandengan_id",
            "penerimaanstokheader.supplier_id",
            "statuscetak.memo as  statuscetak",
            "statuscetak.id as  statuscetak_id",
        );
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

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
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
        });

        $query = DB::table($modelTable);
        $query = $this->select('id',
        'nobukti',
        'tglbukti',
        'penerimaanstok_id',
        'penerimaanstok_nobukti',
        'pengeluaranstok_nobukti',
        'supplier_id',
        'nobon',
        'hutang_nobukti',
        'trado_id',
        'gudang_id',
        'gudangdari_id',
        'gudangke_id',
        'coa',
        'keterangan',
        'statusformat',
        'modifiedby');
        $query = $this->sort($query);
        $models = $this->filter($query);
        
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'penerimaanstok_id',
            'penerimaanstok_nobukti',
            'pengeluaranstok_nobukti',
            'supplier_id',
            'nobon',
            'hutang_nobukti',
            'trado_id',
            'gudang_id',
            'gudangdari_id',
            'gudangke_id',
            'coa',
            'keterangan',
            'statusformat',
            'modifiedby',
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
                    $query = $query->where(function($query){
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            switch ($filters['field']) {
                                case 'penerimaanstok':
                                    $query->where('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                                    break;
                                case 'gudangs':
                                    $query->orWhere('gudangs.gudang', 'LIKE', "%$filters[data]%");
                                    break;
                                case 'trado':
                                    $query->orWhere('trado.keterangan', 'LIKE', "%$filters[data]%");
                                    break;
                                case 'supplier':
                                    $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                                    break;
                                case 'gudangdari':
                                    $query->orWhere('dari.gudang', 'LIKE', "%$filters[data]%");
                                    break;
                                case 'gudangke':
                                    $query->orWhere('ke.gudang', 'LIKE', "%$filters[data]%");
                                    break;
                                case 'penerimaanstok_id_not_null':
                                    break;
                                default:
                                    $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    break;
                            }
                        }
                    });//function query
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'penerimaanstok_id_not_null') {
                            $query = $query->where($this->table . '.penerimaanstok_id', '=', "$filters[data]")->whereRaw(" $this->table.nobukti NOT IN 
                                (SELECT DISTINCT $this->table.penerimaanstok_nobukti
                                FROM penerimaanstokheader
                                WHERE $this->table.penerimaanstok_nobukti IS NOT NULL)
                                ");
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
            $query->where('penerimaanstokheader.statuscetak','<>', request()->cetak)
                  ->whereYear('penerimaanstokheader.tglbukti','=', request()->year)
                  ->whereMonth('penerimaanstokheader.tglbukti','=', request()->month);
            return $query;
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
        ->leftJoin('parameter as statuscetak','penerimaanstokheader.statuscetak','statuscetak.id')
        ->leftJoin('trado as tradodari ','penerimaanstokheader.tradodari_id','tradodari.id')
        ->leftJoin('trado as tradoke ','penerimaanstokheader.tradoke_id','tradoke.id')
        ->leftJoin('gandengan as gandengandari ','penerimaanstokheader.gandengandari_id','gandengandari.id')
        ->leftJoin('gandengan as gandenganke ','penerimaanstokheader.gandenganke_id','gandenganke.id')
        ->leftJoin('gandengan as gandengan ','penerimaanstokheader.gandenganke_id','gandengan.id')
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
