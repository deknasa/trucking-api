<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Menu extends MyModel
{
    use HasFactory;

    protected $table = 'menu';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->selectColumns($query);
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
                $this->table.menuname,
                isnull(menu2.menuname,'') as menuparent,
                $this->table.menuicon,
                isnull(acos.nama,'') as aco_id,
                $this->table.link,
                $this->table.menuexe,
                $this->table.menukode,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("menu as menu2 with (readuncommitted)"), 'menu2.id', '=', 'menu.menuparent')
            ->leftJoin(DB::raw("acos with (readuncommitted)"), 'acos.id', '=', 'menu.aco_id');
    }
    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('menuname', 50)->nullable();
            $table->string('menuparent', 250)->nullable();
            $table->string('menuicon', 50)->nullable();
            $table->string('aco_id', 100)->nullable();
            $table->string('link', 100)->nullable();
            $table->string('menuexe', 100)->nullable();
            $table->string('menukode', 100)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'menuname', 'menuparent', 'menuicon', 'aco_id', 'link', 'menuexe', 'menukode', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                        } else if ($filters['field'] == 'menuparent') {
                            $query = $query->where('menu2.menuname', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'aco_id') {
                            $query = $query->where('acos.nama', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'menuparent') {
                            $query = $query->orWhere('menu2.menuname', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'aco_id') {
                            $query = $query->orWhere('acos.nama', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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

    public function validasiNonController($menuname){
        $validasiQuery = DB::table('menu')
        ->from(
            DB::raw("menu as a with (readuncommitted)")
        )
        ->select(
            'a.aco_id'
        )
        ->where('a.menuname', '=', $menuname)
        ->first();

        return $validasiQuery;
    }
}
