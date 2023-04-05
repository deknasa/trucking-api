<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class PengeluaranTruckingHeader extends MyModel
{
    use HasFactory;
    protected $table = 'pengeluarantruckingheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];  
    
    public function cekvalidasiaksi($nobukti)
    {

        $prosesUangJalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluarantrucking_nobukti'
            )
            ->where('a.pengeluarantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangJalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Proses Uang Jalan Supir',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        

        $penerimaanTrucking = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluarantruckingheader_nobukti'
            )
            ->where('a.pengeluarantruckingheader_nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Trucking',
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }

        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
        ->select(
            'pengeluarantruckingheader.id',
            'pengeluarantruckingheader.nobukti',
            'pengeluarantruckingheader.tglbukti',
            'pengeluarantruckingheader.modifiedby',
            'pengeluarantruckingheader.updated_at',
            'pengeluarantruckingheader.pengeluaran_nobukti',
            'pengeluarantrucking.keterangan as pengeluarantrucking_id',
            'bank.namabank as bank_id',
            DB::raw('(case when (year(pengeluarantruckingheader.tglbukacetak) <= 2000) then null else pengeluarantruckingheader.tglbukacetak end ) as tglbukacetak'),
            'statuscetak.memo as statuscetak',
            'pengeluarantruckingheader.userbukacetak',
            'akunpusat.keterangancoa as coa',
            'statusposting.memo as statusposting'
        )
        // ->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d',strtotime(request()->tgldari)), date('Y-m-d',strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id','pengeluarantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)") , 'pengeluarantruckingheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusposting with (readuncommitted)"), 'pengeluarantruckingheader.statusposting', 'statusposting.id');
            

            if (request()->tgldari) {
                $query->whereBetween('pengeluarantruckingheader.tglbukti', [date('Y-m-d',strtotime(request()->tgldari)), date('Y-m-d',strtotime(request()->tglsampai))]);
            }
            if (request()->pengeluaranheader_id) {
                $query->where('pengeluarantruckingheader.pengeluarantrucking_id',request()->pengeluaranheader_id);
            }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $query = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
        ->select(
            'pengeluarantruckingheader.id',
            'pengeluarantruckingheader.nobukti',
            'pengeluarantruckingheader.tglbukti',
            'pengeluarantruckingheader.pengeluarantrucking_id',
            'pengeluarantrucking.keterangan as pengeluarantrucking',
            'pengeluarantrucking.kodepengeluaran as kodepengeluaran',
            'pengeluarantruckingheader.bank_id',
            'bank.namabank as bank',
            'pengeluarantruckingheader.supir_id',
            'pengeluarantruckingheader.supir_id as supirheader_id',
            'supir.namasupir as supir',
            'pengeluarantruckingheader.statusposting',
            'pengeluarantruckingheader.coa',
            'akunpusat.keterangancoa',
            'pengeluarantruckingheader.pengeluaran_nobukti'            
        )
            ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id','pengeluarantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
            ->where('pengeluarantruckingheader.id', '=', $id);
            

        $data = $query->first();

        return $data;
    }

    public function getTarikDeposito($supir_id)
    {
        // return $supir_id;
        $tempPribadi = $this->createTempTarikDeposito($supir_id);
        PengeluaranTruckingDetail::from(DB::raw("$tempPribadi with (readuncommitted)"))->get();

        $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By penerimaantruckingdetail.nobukti) as id,penerimaantruckingheader.tglbukti,penerimaantruckingdetail.nobukti,penerimaantruckingdetail.keterangan," . $tempPribadi . ".sisa, ". $tempPribadi . ".bayar"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.nobukti', "penerimaantruckingheader.nobukti")
            ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id")
            ->whereRaw("penerimaantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa <> 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->orderBy('penerimaantruckingheader.tglbukti', 'asc')
            ->orderBy('penerimaantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function createTempTarikDeposito($supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("penerimaantruckingdetail.nobukti, SUM(pengeluarantruckingdetail.nominal) as bayar ,(SELECT (penerimaantruckingdetail.nominal - coalesce(SUM(pengeluarantruckingdetail.nominal),0)) FROM pengeluarantruckingdetail WHERE pengeluarantruckingdetail.penerimaantruckingheader_nobukti= penerimaantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.penerimaantruckingheader_nobukti', 'penerimaantruckingdetail.nobukti')
            ->whereRaw("penerimaantruckingdetail.supir_id = $supir_id") 
            ->where("penerimaantruckingdetail.nobukti",  'LIKE', "%DPO%")
            ->groupBy('penerimaantruckingdetail.nobukti','penerimaantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });
        // return $fetch->get();
        $tes = DB::table($temp)->insertUsing(['nobukti', 'bayar', 'sisa'], $fetch);


        return $temp;
    }

    // public function getTarikDeposito($id){
    //     $penerimaantrucking = DB::table($this->table)->from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan','DPO')->first();
    //     // return $pengeluarantruckingheader->id;
    //     $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
    //     ->select(
    //         DB::raw("row_number() Over(Order By pengeluarantruckingdetail.id) as id"),
    //         // 'pengeluarantruckingheader.id',
    //         'pengeluarantruckingdetail.penerimaantruckingheader_nobukti as nobukti',
    //         // 'pengeluarantruckingdetail.tglbukti',
    //         'pengeluarantruckingdetail.keterangan',
    //         'pengeluarantruckingdetail.nominal'
    //     )
    //     ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id',$id);
       
        
    //     return $query->get();
    // }

    // public function getPinjaman($supir_id)
    // {
    //     $penerimaantrucking = DB::table($this->table)->from(DB::raw("pengeluarantrucking with (readuncommitted)"))->where('kodepengeluaran','PJT')->first();
    //     // return response($penerimaantrucking->id,422);
    //     $query = DB::table($this->table)->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
    //     ->select(
    //         DB::raw("row_number() Over(Order By pengeluarantruckingheader.id) as id"),
    //         'pengeluarantruckingheader.nobukti',
    //         'pengeluarantruckingheader.tglbukti',
    //         'pengeluarantruckingdetail.keterangan',
    //         // 'pengeluarantruckingdetail.nominal',
    //         DB::raw("sum(pengeluarantruckingdetail.nominal) as nominal")
    //     )
    //     ->where('pengeluarantruckingheader.pengeluarantrucking_id',$penerimaantrucking->id)
    //     ->where('pengeluarantruckingdetail.supir_id',$supir_id)
    //     ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.pengeluarantruckingheader_id','pengeluarantruckingheader.id')
    //     ->groupBy(
    //         'pengeluarantruckingheader.id',
    //         'pengeluarantruckingheader.nobukti',
    //         'pengeluarantruckingheader.tglbukti',
    //         'pengeluarantruckingdetail.keterangan',
    //     );

    //     return $query->get();
    // }
    public function pengeluarantruckingdetail() {
        return $this->hasMany(PengeluaranTruckingDetail::class, 'pengeluarantruckingheader_id');
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
            "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'pengeluarantrucking.keterangan as pengeluarantrucking_id',
            'bank.namabank as bank_id',
            'statusposting.text as statusposting',
            'statuscetak.memo as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.coa,
            $this->table.pengeluaran_nobukti,
            $this->table.modifiedby,
            $this->table.updated_at"
            )
        )
        ->leftJoin('pengeluarantrucking', 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
        ->leftJoin('bank', 'pengeluarantruckingheader.bank_id', 'bank.id')
        ->leftJoin('parameter as statuscetak' , 'pengeluarantruckingheader.statuscetak', 'statuscetak.id')
        ->leftJoin('parameter as statusposting' , 'pengeluarantruckingheader.statusposting', 'statusposting.id');

    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pengeluarantrucking_id', 1000)->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->string('statusposting', 1000)->nullable();
            $table->string('statuscetak',1000)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        if (request()->tgldariheader) {
            $models  = $query->whereBetween('tglbukti', [date('Y-m-d',strtotime(request()->tgldariheader)), date('Y-m-d',strtotime(request()->tglsampaiheader))]);
        }
        if (request()->pengeluaranheader_id) {
            $query->where('pengeluarantrucking_id',request()->pengeluaranheader_id);
        }
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','pengeluarantrucking_id','bank_id','statusposting','statuscetak','userbukacetak','tglbukacetak','coa','pengeluaran_nobukti','modifiedby','updated_at'],$models);


        return  $temp;         

    }

    public function sort($query)
    {
        if($this->params['sortIndex'] == 'pengeluarantrucking_id') {
            return $query->orderBy('pengeluarantrucking.keterangan', $this->params['sortOrder']);
        } else if($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else{
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                         if ($filters['field'] == 'pengeluarantrucking_id') {
                            $query = $query->where('pengeluarantrucking.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusposting') {
                            $query = $query->where('statusposting.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function($query){
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'pengeluarantrucking_id') {
                                $query->orWhere('pengeluarantrucking.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusposting') {
                                $query->orWhere('statusposting.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            } else {
                                $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('pengeluarantruckingheader.statuscetak','<>', request()->cetak)
                  ->whereYear('pengeluarantruckingheader.tglbukti','=', request()->year)
                  ->whereMonth('pengeluarantruckingheader.tglbukti','=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
