<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PencairanGiroPengeluaranHeader extends MyModel
{
    use HasFactory;

    protected $table = 'PencairanGiroPengeluaranHeader';
    protected $anotherTable = 'pengeluaranheader';
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
        
        $query = DB::table($this->anotherTable)->select(DB::raw("pengeluaranheader.nobukti,pengeluaranheader.id, pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac,
        pengeluarandetail.tgljatuhtempo, pengeluarandetail.nowarkat, (SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluarandetail.alatbayar_id=2) as nominal")
        )
        ->distinct('pengeluaranheader.nobukti')
        ->leftJoin('pengeluarandetail','pengeluarandetail.nobukti','pengeluaranheader.nobukti')
        ->leftJoin('bank','pengeluaranheader.bank_id','bank.id')
        ->where('pengeluarandetail.alatbayar','2');

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
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            pelanggan.namapelanggan as pelanggan_id,
            bank.namabank as bank_id,
            $this->table.keterangan,
            $this->table.postingdari,
            $this->table.diterimadari,
            $this->table.tgllunas,
            cabang.namacabang as cabang_id,
            statuskas.text as statuskas,
            statusapproval.text as statusapproval,
            $this->table.userapproval,
            $this->table.tglapproval,
            $this->table.noresi,
            statusberkas.text as statusberkas,
            $this->table.userberkas,
            $this->table.tglberkas,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
            ->leftJoin('pelanggan', 'penerimaanheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin('bank', 'penerimaanheader.bank_id', 'bank.id')
            ->leftJoin('cabang', 'penerimaanheader.cabang_id', 'cabang.id')
            ->leftJoin('parameter as statuskas', 'penerimaanheader.statuskas', 'statuskas.id')
            ->leftJoin('parameter as statusapproval', 'penerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statusberkas', 'penerimaanheader.statusberkas', 'statusberkas.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti', 1000)->default('1900/1/1');
            $table->string('pelanggan_id', 1000)->default('');
            $table->string('bank_id', 1000)->default('');
            $table->string('keterangan', 3000)->default('');
            $table->string('postingdari', 1000)->default('');
            $table->string('diterimadari', 1000)->default('');
            $table->date('tgllunas', 1000)->default('1900/1/1');
            $table->string('cabang_id', 1000)->default('');
            $table->string('statuskas', 1000)->default('');
            $table->string('statusapproval', 1000)->default('');
            $table->string('userapproval', 1000)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->string('noresi', 1000)->default('');
            $table->string('statusberkas', 1000)->default('')->nullable();
            $table->string('userberkas', 1000)->default('');
            $table->dateTime('tglberkas')->default('1900/1/1');
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
        DB::table($temp)->insertUsing([
            'id', 'nobukti', 'tglbukti', 'pelanggan_id', 'bank_id', 'keterangan', 'postingdari',
            'diterimadari', 'tgllunas', 'cabang_id',  'statuskas', 'statusapproval', 'userapproval', 'tglapproval', 'noresi', 'statusberkas', 'userberkas', 'tglberkas', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


        return  $temp;
    }


    public function sort($query)
    {
        return $query->orderBy($this->anotherTable . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->anotherTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->anotherTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
