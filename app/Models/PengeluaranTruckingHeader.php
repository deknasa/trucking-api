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

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'pengeluarantruckingheader.id',
            'pengeluarantruckingheader.nobukti',
            'pengeluarantruckingheader.tglbukti',
            'pengeluarantruckingheader.keterangan',
            'pengeluarantruckingheader.modifiedby',
            'pengeluarantruckingheader.updated_at',

            'pengeluarantrucking.kodepengeluaran as pengeluarantrucking_id',
            'pengeluaranheader.nobukti as pengeluaran_nobukti',
            'pengeluaranheader.tglbukti as pengeluaran_tgl',

            'bank.namabank as bank_id',
            
            'akunpusat.coa as coa',
            'statusposting.text as statusposting'
        )
            ->leftJoin('pengeluarantrucking', 'pengeluarantruckingheader.pengeluarantrucking_id','pengeluarantrucking.id')
            ->leftJoin('pengeluaranheader', 'pengeluarantruckingheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin('bank', 'pengeluarantruckingheader.bank_id', 'bank.id')
            ->leftJoin('akunpusat', 'pengeluarantruckingheader.coa', 'akunpusat.coa')
            ->leftJoin('parameter as statusposting' , 'pengeluarantruckingheader.statusposting', 'statusposting.id');
            


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

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
            $this->table.keterangan,
            'pengeluarantrucking.kodepengeluaran as pengeluarantrucking_id',
            'pengeluaranheader.nobukti as pengeluaran_nobukti',
            'pengeluaranheader.tglbukti as pengeluaran_tgl',
            'bank.namabank as bank_id',
            'akunpusat.coa as coa',
            'statusposting.text as statusposting',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at,
            $this->table.proses_nobukti,
            $this->table.statusformat"
            )
        )
        ->leftJoin('pengeluarantrucking', 'pengeluarantruckingheader.pengeluarantrucking_id', 'pengeluarantrucking.id')
        ->leftJoin('pengeluaranheader', 'pengeluarantruckingheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
        ->leftJoin('bank', 'pengeluarantruckingheader.bank_id', 'bank.id')
        ->leftJoin('akunpusat', 'pengeluarantruckingheader.coa', 'akunpusat.coa')
        ->leftJoin('parameter as statusposting' , 'pengeluarantruckingheader.statusposting', 'statusposting.id');

    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('pengeluarantrucking_id', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('bank_id', 1000)->default('');
            $table->string('statusposting', 1000)->default('');
            $table->string('coa', 1000)->default('');
            $table->string('pengeluaran_nobukti', 1000)->default('');
            $table->string('pengeluaran_tgl', 1000)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->string('proses_nobukti', 1000)->default('');
            $table->bigInteger('statusformat')->default('');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','pengeluarantrucking_id','keterangan','bank_id','statusposting','coa','pengeluaran_nobukti','pengeluaran_tgl','modifiedby','created_at','updated_at','proses_nobukti','statusformat'],$models);


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
                            $query = $query->where('pengeluarantrucking.kodepengeluaran', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusposting') {
                            $query = $query->where('statusposting.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                         if ($filters['field'] == 'pengeluarantrucking_id') {
                            $query = $query->orWhere('pengeluarantrucking.kodepengeluaran', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusposting') {
                            $query = $query->orWhere('statusposting.text', 'LIKE', "%$filters[data]%");
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

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
