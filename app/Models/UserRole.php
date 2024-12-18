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

    public function get($userid)
    {
        $this->setRequestParameters();
        $query = DB::table("userrole")->from(DB::raw("userrole"))
            ->select('userrole.id', 'role.rolename', 'userrole.modifiedby', 'userrole.created_at', 'userrole.updated_at')
            ->leftJoin(DB::raw("role with (readuncommitted)"), 'userrole.role_id', 'role.id')
            ->where('userrole.user_id', $userid);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        return $query->get();
    }

    public function sort($query)
    {
        return $query->orderBy($this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            // if ($filters['field']) {
                            //     if (in_array($filters['field'], ['modifiedby', 'created_at', 'updated_at'])) {
                            //         $query = $query->where('userrole.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            //     } else {
                            if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(userrole." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'rolename') {
                                $query = $query->where('role.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->where('userrole.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                            // }
                        }
                    });

                    break;
                case "OR":

                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            // if (in_array($filters['field'], ['modifiedby', 'created_at', 'updated_at'])) {
                            //     $query = $query->orWhere('userrole.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            // } else {
                            if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(userrole." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'rolename') {
                                $query = $query->orWhere('role.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->orWhere('userrole.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

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

    public function processDestroy($id): UserRole
    {
        $userRole = new UserRole();
        $userRole = $userRole->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($userRole->getTable()),
            'postingdari' => 'DELETE PARAMETER',
            'idtrans' => $userRole->id,
            'nobuktitrans' => $userRole->id,
            'aksi' => 'DELETE',
            'datajson' => $userRole->toArray(),
            'modifiedby' => $userRole->modifiedby
        ]);

        return $userRole;
    }
    public function processStore($data)
    {
        $userRole = new UserRole();
        $userRole->role_id = $data['role_id'];
        $userRole->user_id = $data['user_id'];
        $userRole->modifiedby = auth('api')->user()->name;
        $userRole->info = html_entity_decode(request()->info);

        if (!$userRole->save()) {
            throw new \Exception("Error storing user role.");
        }

        return $userRole;
    }
}
