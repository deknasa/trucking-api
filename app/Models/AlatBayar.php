<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\RestrictDeletion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AlatBayar extends MyModel
{
    use HasFactory, RestrictDeletion;

    protected $table = 'alatbayar';

    // protected $casts = [
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];
    
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'alatbayar.id',
            'alatbayar.kodealatbayar',
            'alatbayar.namaalatbayar',
            'alatbayar.keterangan',
            'parameter_statuslangsunggcair.text as statuslangsunggcair',
            'parameter_statusdefault.text as statusdefault',
            'bank.namabank as bank_id',
            'alatbayar.modifiedby',
            'alatbayar.created_at',
            'alatbayar.updated_at'
        )
            ->leftJoin('bank', 'alatbayar.bank_id', 'bank.id')
            ->leftJoin('parameter as parameter_statuslangsunggcair', 'alatbayar.statuslangsunggcair', 'parameter_statuslangsunggcair.id')
            ->leftJoin('parameter as parameter_statusdefault', 'alatbayar.statusdefault', 'parameter_statusdefault.id');

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
            DB::raw("
                $this->table.id,
                $this->table.kodealatbayar,
                $this->table.namaalatbayar,
                $this->table.keterangan,
                'parameter_statuslangsunggcair.text as statuslangsunggcair',
                'parameter_statusdefault.text as statusdefault',
                'bank.namabank as bank_id',
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
        )
        ->leftJoin('bank', 'alatbayar.bank_id', 'bank.id')
        ->leftJoin('parameter as parameter_statuslangsunggcair', 'alatbayar.statuslangsunggcair', 'parameter_statuslangsunggcair.id')
        ->leftJoin('parameter as parameter_statusdefault', 'alatbayar.statusdefault', 'parameter_statusdefault.id');

    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table){
            $table->bigInteger('id')->default('0');
            $table->string('kodealatbayar', 1000)->default('');
            $table->string('namaalatbayar', 1000)->default('');
            $table->string('keterangan', 1000)->default('');
            $table->string('statuslangsungcair')->default('');
            $table->string('statusdefault')->default('');
            $table->string('bank_id')->default('');
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id','kodealatbayar','namaalatbayar','keterangan','statuslangsungcair','statusdefault','bank_id','modifiedby','created_at','updated_at'], $models);

        return $temp;
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
                        if ($filters['field'] == 'statuslangsunggcair') {
                            $query = $query->where('parameter_statuslangsunggcair.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusdefault') {
                            $query = $query->where('parameter_statusdefault.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuslangsunggcair') {
                            $query = $query->orWhere('parameter_statuslangsunggcair.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusdefault') {
                            $query = $query->orWhere('parameter_statusdefault.text', '=', $filters['data']);
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
