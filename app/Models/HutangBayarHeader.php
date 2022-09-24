<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class HutangBayarHeader extends MyModel
{
    use HasFactory;
    protected $table = 'hutangbayarheader';

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
            'hutangbayarheader.id',
            'hutangbayarheader.nobukti',
            'hutangbayarheader.tglbukti',
            'hutangbayarheader.keterangan',
            'hutangbayarheader.modifiedby',
            'hutangbayarheader.updated_at',

            'bank.namabank as bank_id',
            'supplier.namasupplier as supplier_id',
            'akunpusat.coa as coa',

        )
            ->leftJoin('bank', 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin('akunpusat', 'hutangbayarheader.coa', 'akunpusat.coa')
            ->leftJoin('supplier', 'hutangbayarheader.supplier_id', 'supplier.id');

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


        $query = DB::table('hutangbayarheader')->select(
            'hutangbayarheader.id',
            'hutangbayarheader.nobukti',
            'hutangbayarheader.tglbukti',
            'hutangbayarheader.keterangan',
            'hutangbayarheader.modifiedby',
            'hutangbayarheader.updated_at',
            'bank.namabank as bank',
            'bank.id as bank_id',

            
            'akunpusat.coa as akunpusat',
            'supplier.namasupplier as supplier',
            'supplier.id as supplier_id',

        )
            ->leftJoin('bank', 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin('akunpusat', 'hutangbayarheader.coa', 'akunpusat.coa')
            ->leftJoin('supplier', 'hutangbayarheader.supplier_id', 'supplier.id')

            ->where('hutangbayarheader.id', $id);


        $data = $query->first();

        return $data;
    }

    public function hutangbayardetail()
    {
        return $this->hasMany(HutangBayarDetail::class, 'hutangbayar_id');
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            $this->table.keterangan,
            'bank.namabank as bank_id',
            'supplier.namasupplier as supplier_id',
            'akunpusat.coa as coa',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at,
            $this->table.statusformat"
            )
            //'hutangbayar.nobukti as pengeluaran_nobukti',
            
        )
            //  ->leftJoin('hutangbayar', 'penerimaantruckingheader.penerimaan_nobukti', 'hutangbayar.nobukti')
            ->leftJoin('bank', 'hutangbayarheader.bank_id', 'bank.id')
            ->leftJoin('supplier', 'hutangbayarheader.supplier_id', 'supplier.id')

            ->leftJoin('akunpusat', 'hutangbayarheader.coa', 'akunpusat.coa');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('bank_id', 1000)->default('');
            $table->string('supplier_id', 1000)->default('');
            $table->string('coa', 1000)->default('');

            // $table->string('pengeluaran_nobukti', 1000)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->bigInteger('statusformat')->default('');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'keterangan', 'bank_id', 'supplier_id', 'coa', 'modifiedby', 'created_at', 'updated_at', 'statusformat'], $models);


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
                        if ($filters['field'] == 'hutangbayar_id') {
                            $query = $query->where('hutangbayar.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'hutangbayar_id') {
                            $query = $query->orWhere('hutangbayar.nobukti', 'LIKE', "%$filters[data]%");
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
