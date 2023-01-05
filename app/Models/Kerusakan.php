<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Kerusakan extends MyModel
{
    use HasFactory;

    protected $table = 'kerusakan';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // protected $casts = [
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ]; 

    public function get()
    {
        $this->setRequestParameters();

        $query = Kerusakan::from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'kerusakan.id',
                'kerusakan.keterangan',
                'parameter.memo as statusaktif',
                'kerusakan.modifiedby',
                'kerusakan.created_at',
                'kerusakan.updated_at'
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kerusakan.statusaktif', '=', 'parameter.id');

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
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.keterangan,
            'parameter.text as statusaktif',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin('parameter', 'kerusakan.statusaktif', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('keterangan', 1000)->default('');
            $table->string('statusaktif', 1000)->default('');
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
        DB::table($temp)->insertUsing(['id', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else {
                            $query = $query->where('kerusakan.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                        } else {
                            $query = $query->orWhere('kerusakan.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
