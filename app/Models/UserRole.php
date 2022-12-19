<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;



class UserRole extends MyModel
{
    use HasFactory;

    protected $table = 'userrole';

    protected $guarded = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    // public function get()
    // {
    //     $this->setRequestParameters();

    //     $query = DB::table($this->table)->select(
    //         DB::raw("userrole.rolename as rolename,
    //                     acl.role_id as role_id,
    //                     min(acl.id) as id_,
    //                     max(acl.modifiedby) as modifiedby,
    //                     max(acl.created_at) as created_at,
    //                         max(acl.updated_at) as updated_at")
    //     )
    //         ->Join('role', 'acl.role_id', '=', 'role.id')
    //         ->groupby('acl.role_id', 'role.rolename');

    //     $this->totalRows = $query->count();
    //     $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

    //     $this->sort($query);
    //     $this->filter($query);
    //     $this->paginate($query);

    //     $data = $query->get();

    //     return $data;
    // }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.user_id,
            $this->table.role_id,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        );
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->unsignedBigInteger('user_id')->default('0');
            $table->unsignedBigInteger('role_id')->default('0');
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
        DB::table($temp)->insertUsing(['id', 'user_id', 'role_id',  'modifiedby', 'created_at', 'updated_at'], $models);


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
                        if ($filters['field'] == 'user_id') {
                            $query = $query->where('userrole.user_id', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'user_id') {
                            $query = $query->orWhere('userrole.user_id', 'LIKE', "%$filters[data]%");
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
