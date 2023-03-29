<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class PenerimaanTruckingHeader extends MyModel
{
    use HasFactory;
    protected $table = 'penerimaantruckingheader';

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
                'a.penerimaantrucking_nobukti'
            )
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangJalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Proses Uang Jalan Supir',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        
        $pengeluaranTrucking = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaantruckingheader_nobukti'
            )
            ->where('a.penerimaantruckingheader_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengeluaranTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Trucking',
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

        $query = DB::table($this->table)->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
        ->select(
            'penerimaantruckingheader.id',
            'penerimaantruckingheader.nobukti',
            'penerimaantruckingheader.tglbukti',

            'penerimaantrucking.keterangan as penerimaantrucking_id',
            'penerimaantruckingheader.penerimaan_nobukti',

            'bank.namabank as bank_id',
            DB::raw('(case when (year(penerimaantruckingheader.tglbukacetak) <= 2000) then null else penerimaantruckingheader.tglbukacetak end ) as tglbukacetak'),
            'parameter.memo as statuscetak',
            'penerimaantruckingheader.userbukacetak',
            'penerimaantruckingheader.jumlahcetak',
            'akunpusat.keterangancoa as coa',
            'penerimaantruckingheader.modifiedby',
            'penerimaantruckingheader.created_at',
            'penerimaantruckingheader.updated_at',
        )
        ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), 'penerimaantruckingheader.penerimaantrucking_id','penerimaantrucking.id')
        ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaantruckingheader.coa','akunpusat.coa')
        ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'penerimaantruckingheader.statuscetak','parameter.id')
        ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id');
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

    public function findAll($id)
    {

        $query = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
        ->select(
            'penerimaantruckingheader.id',
            'penerimaantruckingheader.nobukti',
            'penerimaantruckingheader.tglbukti',
            'penerimaantruckingheader.penerimaantrucking_id',
            'penerimaantrucking.kodepenerimaan as penerimaantrucking',
            'penerimaantruckingheader.bank_id',
            'bank.namabank as bank',
            'penerimaantruckingheader.coa',
            'akunpusat.keterangancoa',
            'penerimaantruckingheader.penerimaan_nobukti'
        )
            ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), 'penerimaantruckingheader.penerimaantrucking_id','penerimaantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaantruckingheader.coa', 'akunpusat.coa')
            ->where('penerimaantruckingheader.id', '=', $id);
            

        $data = $query->first();

        return $data;
    }

    public function penerimaantruckingdetail() {
        return $this->hasMany(PenerimaanTruckingDetail::class, 'penerimaantruckingheader_id');
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
            "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'penerimaantrucking.keterangan as penerimaantrucking_id',
            'bank.namabank as bank_id',
            $this->table.coa,
            $this->table.penerimaan_nobukti,
            'parameter.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
        ->leftJoin('penerimaantrucking', 'penerimaantruckingheader.penerimaantrucking_id', 'penerimaantrucking.id')
        ->leftJoin('parameter' , 'penerimaantruckingheader.statuscetak', 'parameter.id')
        ->leftJoin('bank', 'penerimaantruckingheader.bank_id', 'bank.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('penerimaantrucking_id', 1000)->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->string('coa', 1000)->nullable();
            $table->string('penerimaan_nobukti', 1000)->nullable();
            $table->string('statuscetak',1000)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d',strtotime(request()->tgldariheader )), date('Y-m-d',strtotime(request()->tglsampaiheader ))]);
        }
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','penerimaantrucking_id','bank_id','coa','penerimaan_nobukti','statuscetak','userbukacetak','tglbukacetak','jumlahcetak', 'modifiedby','created_at','updated_at'],$models);


        return  $temp;         

    }

    public function getDeposito($supir_id){
        $penerimaantrucking = DB::table($this->table)->from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan','DPO')->first();
        // return $penerimaantruckingheader->id;
        $query = DB::table($this->table)->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
        ->select(
            DB::raw("row_number() Over(Order By penerimaantruckingheader.id) as id"),
            // 'penerimaantruckingheader.id',
            'penerimaantruckingheader.nobukti',
            'penerimaantruckingheader.tglbukti',
            'penerimaantruckingdetail.keterangan',
            DB::raw("sum(penerimaantruckingdetail.nominal) as nominal")
        )
        ->where('penerimaantruckingheader.penerimaantrucking_id',$penerimaantrucking->id)
        ->where('penerimaantruckingheader.supir_id',$supir_id)
        ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.penerimaantruckingheader_id','penerimaantruckingheader.id')
        ->groupBy(
            'penerimaantruckingheader.id',
            'penerimaantruckingheader.nobukti',
            'penerimaantruckingheader.tglbukti',
            'penerimaantruckingdetail.keterangan',
        );
        
        return $query->get();
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
                         if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'penerimaantrucking_id') {
                            $query = $query->where('penerimaantrucking.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->where($this->table . '.tglbukti', '=', date('Y-m-d',strtotime($filters['data'])));
                            
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                     $query = $query->where(function($query){
                         foreach ($this->params['filters']['rules'] as $index => $filters) {
                              if ($filters['field'] == 'statuscetak') {
                                 $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                             } else if ($filters['field'] == 'penerimaantrucking_id') {
                                 $query = $query->orWhere('penerimaantrucking.keterangan', 'LIKE', "%$filters[data]%");
                             } else if ($filters['field'] == 'tglbukti') {
                                $query->orWhere($this->table . '.tglbukti', '=', date('Y-m-d',strtotime($filters['data'])));
                                
                            } else if ($filters['field'] == 'bank_id') {
                                 $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                             } else {
                                 $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('penerimaantruckingheader.statuscetak','<>', request()->cetak)
                  ->whereYear('penerimaantruckingheader.tglbukti','=', request()->year)
                  ->whereMonth('penerimaantruckingheader.tglbukti','=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
