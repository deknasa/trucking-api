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
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $rtb = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
        
        ->leftJoin('gudang as gudangs','penerimaanstokheader.gudang_id','gudangs.id')
        ->leftJoin('gudang as dari','penerimaanstokheader.gudangdari_id','dari.id')
        ->leftJoin('gudang as ke','penerimaanstokheader.gudangke_id','ke.id')
        ->leftJoin('parameter as statuscetak','penerimaanstokheader.statuscetak','statuscetak.id')
        ->leftJoin('penerimaanstok','penerimaanstokheader.penerimaanstok_id','penerimaanstok.id')
        ->leftJoin('akunpusat','penerimaanstokheader.coa','akunpusat.coa')
        ->leftJoin('trado','penerimaanstokheader.trado_id','trado.id')
        ->leftJoin('trado as tradodari ','penerimaanstokheader.tradodari_id','tradodari.id')
        ->leftJoin('trado as tradoke ','penerimaanstokheader.tradoke_id','tradoke.id')
        ->leftJoin('gandengan as gandengandari ','penerimaanstokheader.gandengandari_id','gandengandari.id')
        ->leftJoin('gandengan as gandenganke ','penerimaanstokheader.gandenganke_id','gandenganke.id')
        ->leftJoin('gandengan as gandengan ','penerimaanstokheader.gandenganke_id','gandengan.id')
        ->leftJoin('penerimaanstokheader as nobuktipenerimaanstok','nobuktipenerimaanstok.nobukti','penerimaanstokheader.penerimaanstok_nobukti')
        ->leftJoin('penerimaanstokheader as nobuktispb','penerimaanstokheader.nobukti','nobuktispb.penerimaanstok_nobukti')
        ->leftJoin('supplier','penerimaanstokheader.supplier_id','supplier.id');
        if (request()->penerimaanstok_id==$spb->text) {
            
            // $query->leftJoin('penerimaanstokheader as po', 'penerimaanstokheader.penerimaanstok_nobukti', '=', 'po.nobukti')
            $query->where('penerimaanstokheader.penerimaanstok_id', '=', $po->text)
            ->whereNotIn('penerimaanstokheader.nobukti', function($query) {
                $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                      ->from('penerimaanstokheader')
                      ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                      ->where('penerimaanstokheader.penerimaanstok_nobukti','!=','');
            });
            // return $query->get();
        }

        if (request()->penerimaanstok_id==$spbs->text) {
            
            // $query->leftJoin('penerimaanstokheader as po', 'penerimaanstokheader.penerimaanstok_nobukti', '=', 'po.nobukti')
            $query->where('penerimaanstokheader.penerimaanstok_id', '=', $do->text)
            ->whereNotIn('penerimaanstokheader.nobukti', function($query) {
                $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                      ->from('penerimaanstokheader')
                      ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                      ->where('penerimaanstokheader.penerimaanstok_nobukti','!=','');
            });
            // return $query->get();
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
        if (request()->tgldari) {
            $query->whereBetween('penerimaanstokheader.tglbukti', [date('Y-m-d',strtotime(request()->tgldari)), date('Y-m-d',strtotime(request()->tglsampai))]);
        }
        if (request()->penerimaanheader_id) {
            $query->where('penerimaanstokheader.penerimaanstok_id',request()->penerimaanheader_id);
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
        $po = Parameter::where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
        $penerimaanstok_nobukti = $this->table.".penerimaanstok_nobukti";
        if (request()->penerimaanheader_id==$po->text) {
            $penerimaanstok_nobukti = "nobuktispb.nobukti as penerimaanstok_nobukti";
        }
            
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "penerimaanstok.kodepenerimaan as penerimaanstok",
            $penerimaanstok_nobukti,
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
            "akunpusat.keterangancoa as coa",
            "$this->table.keterangan",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
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
            "nobuktipenerimaanstok.tglbukti as parrenttglbukti",
            "statuscetak.id as  statuscetak_id",
        );
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();            
            $table->unsignedBigInteger('penerimaanstok_id')->nullable();
            $table->string('penerimaanstok_nobukti',50)->nullable();
            $table->string('pengeluaranstok_nobukti',50)->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();            
            $table->string('nobon', 50)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('gudangdari_id')->nullable();
            $table->unsignedBigInteger('gudangke_id')->nullable();            
            $table->string('coa',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();   
            $table->string('modifiedby',50)->nullable();
            $table->increments('position');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $query = DB::table($modelTable);
        $query = $this->select(
            "$modelTable.id",
            "$modelTable.nobukti",
            "$modelTable.tglbukti",
            "$modelTable.penerimaanstok_id",
            "$modelTable.penerimaanstok_nobukti",
            "$modelTable.pengeluaranstok_nobukti",
            "$modelTable.supplier_id",
            "$modelTable.nobon",
            "$modelTable.hutang_nobukti",
            "$modelTable.trado_id",
            "$modelTable.gudang_id",
            "$modelTable.gudangdari_id",
            "$modelTable.gudangke_id",
            "$modelTable.coa",
            "$modelTable.keterangan",
            "$modelTable.statusformat",
            "$modelTable.modifiedby")
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
        $query = $this->sort($query);
        $models = $this->filter($query);
        if (request()->tgldariheader) {
            $models->whereBetween('tglbukti', [date('Y-m-d',strtotime(request()->tgldariheader)), date('Y-m-d',strtotime(request()->tglsampaiheader))]);
        }
        if (request()->penerimaanheader_id) {
            $models->where('penerimaanstok_id',request()->penerimaanstok_id);
        }
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

        if ($this->params['sortIndex'] == 'penerimaanstok') {
            return $query->orderBy('penerimaanstok.kodepenerimaan', $this->params['sortOrder']);    
        } else if($this->params['sortIndex'] == 'gudangs'){
            return $query->orderBy('gudangs.gudang', $this->params['sortOrder']);    
        } else if($this->params['sortIndex'] == 'trado'){
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);    
        } else if($this->params['sortIndex'] == 'supplier'){
            return $query->orderBy('supplier.namasupplier', $this->params['sortOrder']);    
        } else if($this->params['sortIndex'] == 'gudangdari'){
            return $query->orderBy('dari.gudang', $this->params['sortOrder']);    
        } else if($this->params['sortIndex'] == 'gudangke'){
            return $query->orderBy('ke.gudang', $this->params['sortOrder']);    
        } else if($this->params['sortIndex'] == 'coa'){
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);    
        }
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'penerimaanstok') {
                            $query = $query->where('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'gudangs') {
                            $query = $query->where('gudangs.gudang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'trado') {
                            $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supplier') {
                            $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'gudangdari') {
                            $query = $query->where('dari.gudang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'gudangke') {
                            $query = $query->where('ke.gudang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function($query){
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'penerimaanstok') {
                                $query = $query->orWhere('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gudangs') {
                                $query = $query->orWhere('gudangs.gudang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'trado') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supplier') {
                                $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gudangdari') {
                                $query = $query->orWhere('dari.gudang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gudangke') {
                                $query = $query->orWhere('ke.gudang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });
                    //function query
                    // foreach ($this->params['filters']['rules'] as $index => $filters) {
                    //     if ($filters['field'] == 'penerimaanstok_id_not_null') {
                    //         $query = $query->where($this->table . '.penerimaanstok_id', '=', "$filters[data]")->whereRaw(" $this->table.nobukti NOT IN 
                    //             (SELECT DISTINCT $this->table.penerimaanstok_nobukti
                    //             FROM penerimaanstokheader
                    //             WHERE $this->table.penerimaanstok_nobukti IS NOT NULL)
                    //             ");
                    //     }
                    // }

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
        ->leftJoin('akunpusat','penerimaanstokheader.coa','akunpusat.coa')
        ->leftJoin('gandengan as gandengandari ','penerimaanstokheader.gandengandari_id','gandengandari.id')
        ->leftJoin('gandengan as gandenganke ','penerimaanstokheader.gandenganke_id','gandenganke.id')
        ->leftJoin('gandengan as gandengan ','penerimaanstokheader.gandenganke_id','gandengan.id')
        ->leftJoin('penerimaanstok','penerimaanstokheader.penerimaanstok_id','penerimaanstok.id')
        ->leftJoin('trado','penerimaanstokheader.trado_id','trado.id')
        ->leftJoin('penerimaanstokheader as nobuktipenerimaanstok','nobuktipenerimaanstok.nobukti','penerimaanstokheader.penerimaanstok_nobukti')
        ->leftJoin('supplier','penerimaanstokheader.supplier_id','supplier.id');
        $data = $query->where("$this->table.id",$id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
