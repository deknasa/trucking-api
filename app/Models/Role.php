<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Role extends MyModel
{
    use HasFactory;

    protected $table = 'role';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

            $query = DB::table($this->table)
            ->from(DB::raw($this->table . " with (readuncommitted)"))
            ->select(
                'id',
                'rolename',
                'modifiedby',
                'created_at',
                'updated_at',
                DB::raw("'Laporan Role' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            );

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->selectColumns($query);
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.rolename",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        );
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('rolename', 500)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'rolename',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

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
                        if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function acls()
    {
        return $this->belongsToMany(Aco::class, 'acl')
            ->withTimestamps()
            ->select(
                'acos.id',
                'acos.class',
                'acos.method',
                'acos.nama',
                'acos.modifiedby',
                'acl.created_at',
                'acl.updated_at'
            );
    }

    public function processStore(array $data): Role
    {
        $role = new Role();
        $role->rolename = $data['rolename'];
        $role->modifiedby = auth('api')->user()->user;
        $role->info = html_entity_decode(request()->info);

        if (!$role->save()) {
            throw new \Exception('Error storing role.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($role->getTable()),
            'postingdari' => 'ENTRY ROLE',
            'idtrans' => $role->id,
            'nobuktitrans' => $role->id,
            'aksi' => 'ENTRY',
            'datajson' => $role->toArray(),
            'modifiedby' => $role->modifiedby
        ]);

        return $role;
    }

    public function processUpdate(Role $role, array $data): Role
    {
        $role->rolename = $data['rolename'];
        $role->modifiedby = auth('api')->user()->user;
        $role->info = html_entity_decode(request()->info);

        if (!$role->save()) {
            throw new \Exception('Error updating role.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($role->getTable()),
            'postingdari' => 'EDIT ROLE',
            'idtrans' => $role->id,
            'nobuktitrans' => $role->id,
            'aksi' => 'EDIT',
            'datajson' => $role->toArray(),
            'modifiedby' => $role->modifiedby
        ]);

        return $role;
    }

    public function processDestroy($id): Role
    {
        $role = new Role();
        $role = $role->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($role->getTable()),
            'postingdari' => 'DELETE ROLE',
            'idtrans' => $role->id,
            'nobuktitrans' => $role->id,
            'aksi' => 'DELETE',
            'datajson' => $role->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $role;
    }
}
