<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KasGantungHeader extends MyModel
{
    use HasFactory;

    protected $table = 'kasgantungheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // protected $casts = [
    //     'tgl' => 'date:d-m-Y',
    //     'tglkaskeluar' => 'date:d-m-Y',
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];     

    public function kasgantungDetail() {
        return $this->hasMany(KasGantungDetail::class, 'kasgantung_id');
    }

    // public function bank() {
    //     return $this->belongsTo(Bank::class, 'bank_id');
    // }

    // public function penerima() {
    //     return $this->belongsTo(Penerima::class, 'penerima_id');
    // }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'kasgantungheader.id',
            'kasgantungheader.nobukti',
            'kasgantungheader.tglbukti',
            'penerima.namapenerima as penerima_id',
            'kasgantungheader.keterangan',
            'bank.namabank as bank_id',
            'kasgantungheader.pengeluaran_nobukti',
            'kasgantungheader.coakaskeluar',
            'kasgantungheader.tglkaskeluar',
            'kasgantungheader.postingdari',
            'kasgantungheader.modifiedby',
            'kasgantungheader.created_at',
            'kasgantungheader.updated_at'
        )
            ->leftJoin('penerima', 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin('bank', 'kasgantungheader.bank_id', 'bank.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
        
        return $data;
    }

    public function find($id) 
    {
        $query = DB::table('kasgantungheader')
        ->select(
            'kasgantungheader.id',
            'kasgantungheader.nobukti',
            'kasgantungheader.tglbukti',
            'kasgantungheader.penerima_id',
            'penerima.namapenerima as penerima',
            'kasgantungheader.keterangan',
            'kasgantungheader.bank_id',
            'bank.namabank as bank',
            'kasgantungheader.pengeluaran_nobukti',
            'kasgantungheader.coakaskeluar',
            'kasgantungheader.tglkaskeluar',
            'kasgantungheader.modifiedby',
            'kasgantungheader.created_at',
            'kasgantungheader.updated_at'
        )
            ->leftJoin('penerima', 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin('bank', 'kasgantungheader.bank_id', 'bank.id')
            ->where('kasgantungheader.id',$id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
            "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'penerima.namapenerima as penerima_id',
            $this->table.keterangan,
            'bank.namabank as bank_id',
            $this->table.pengeluaran_nobukti,
            $this->table.coakaskeluar,
            $this->table.tglkaskeluar,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
        ->leftJoin('penerima', 'kasgantungheader.penerima_id', 'penerima.id')
        ->leftJoin('bank', 'kasgantungheader.bank_id', 'bank.id');

    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('penerima_id', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('bank_id', 1000)->default('');
            $table->string('pengeluaran_nobukti', 1000)->default('');
            $table->string('coakaskeluar', 1000)->default('');
            $table->string('tglkaskeluar', 1000)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id','nobukti','tglbukti','penerima_id','keterangan','bank_id','pengeluaran_nobukti','coakaskeluar','tglkaskeluar','modifiedby','created_at','updated_at'],$models);


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
                        if ($filters['field'] == 'penerima_id') {
                            $query = $query->where('penerima.namapenerima', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'penerima_id') {
                            $query = $query->orWhere('penerima.namapenerima', '=', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
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
