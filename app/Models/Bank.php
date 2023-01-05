<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Bank extends MyModel
{
    use HasFactory;

    protected $table = 'bank';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'bank.id',
                'bank.kodebank',
                'bank.namabank',
                'bank.coa',
                'bank.tipe',
                'parameter.memo as statusaktif',
                'statusformatpenerimaan.memo as statusformatpenerimaan',
                'statusformatpengeluaran.memo as statusformatpengeluaran',
                'bank.modifiedby',
                'bank.created_at',
                'bank.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'bank.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusformatpenerimaan with (readuncommitted)"), 'bank.statusformatpenerimaan', '=', 'statusformatpenerimaan.id')
            ->leftJoin(DB::raw("parameter as statusformatpengeluaran with (readuncommitted)"), 'bank.statusformatpengeluaran', '=', 'statusformatpengeluaran.id');

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
        $query =  DB::table('bank')->from(
            DB::raw("bank with (readuncommitted)")
        )
            ->select(
                'bank.id',
                'bank.kodebank',
                'bank.namabank',
                'bank.coa',
                'bank.tipe',
                'bank.statusaktif',
                'bank.statusformatpenerimaan',
                'bank.statusformatpengeluaran',

            )
            ->where('bank.id', $id);

        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.kodebank,
            $this->table.namabank,
            $this->table.coa,
            $this->table.tipe,
            parameter.text as statusaktif,
            statusformatpenerimaan.text as statusformatpenerimaan,
            statusformatpengeluaran.text as statusformatpengeluaran,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'bank.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusformatpenerimaan with (readuncommitted)"), 'bank.statusformatpenerimaan', '=', 'statusformatpenerimaan.id')
            ->leftJoin(DB::raw("parameter as statusformatpengeluaran with (readuncommitted)"), 'bank.statusformatpengeluaran', '=', 'statusformatpengeluaran.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('kodebank', 1000)->default('');
            $table->string('namabank', 1000)->default('');
            $table->string('coa', 1000)->default('');
            $table->string('tipe', 1000)->default('');
            $table->string('statusaktif', 1000)->default('');
            $table->string('statusformatpenerimaan', 1000)->default('');
            $table->string('statusformatpengeluaran', 1000)->default('');
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
        DB::table($temp)->insertUsing(['id', 'kodebank', 'namabank', 'coa', 'tipe', 'statusaktif', 'statusformatpenerimaan', 'statusformatpengeluaran', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusformatpenerimaan') {
                            $query = $query->where('statusformatpenerimaan.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusformatpengeluaran') {
                            $query = $query->where('statusformatpengeluaran.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusformatpenerimaan') {
                            $query = $query->orWhere('statusformatpenerimaan.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusformatpengeluaran') {
                            $query = $query->orWhere('statusformatpengeluaran.text', 'LIKE', "%$filters[data]%");
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
