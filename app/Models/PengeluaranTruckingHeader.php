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
            ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id','pengeluarantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)") , 'pengeluarantruckingheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusposting with (readuncommitted)"), 'pengeluarantruckingheader.statusposting', 'statusposting.id');
            


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
            'pengeluarantruckingheader.bank_id',
            'bank.namabank as bank',
            'pengeluarantruckingheader.statusposting',
            'pengeluarantruckingheader.coa',
            'akunpusat.keterangancoa',
            'pengeluarantruckingheader.pengeluaran_nobukti'            
        )
            ->leftJoin(DB::raw("pengeluarantrucking with (readuncommitted)"), 'pengeluarantruckingheader.pengeluarantrucking_id','pengeluarantrucking.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'pengeluarantruckingheader.coa', 'akunpusat.coa')
            ->where('pengeluarantruckingheader.id', '=', $id);
            

        $data = $query->first();

        return $data;
    }
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
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('pengeluarantrucking_id', 1000)->default('');
            $table->string('bank_id', 1000)->default('');
            $table->string('statusposting', 1000)->default('');
            $table->string('statuscetak',1000)->default('');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->string('coa', 1000)->default('');
            $table->string('pengeluaran_nobukti', 1000)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','pengeluarantrucking_id','bank_id','statusposting','statuscetak','userbukacetak','tglbukacetak','coa','pengeluaran_nobukti','modifiedby','updated_at'],$models);


        return  $temp;         

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
                         if ($filters['field'] == 'pengeluarantrucking_id') {
                            $query = $query->where('pengeluarantrucking.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
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
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                         if ($filters['field'] == 'pengeluarantrucking_id') {
                            $query = $query->orWhere('pengeluarantrucking.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusposting') {
                            $query = $query->orWhere('statusposting.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
