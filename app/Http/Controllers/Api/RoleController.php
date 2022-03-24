<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\DestroyRoleRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\Role;
use App\Models\LogTrail;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 100,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Role::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = Role::select(
                'role.id',
                'role.rolename',
                'role.modifiedby',
                'role.created_at',
                'role.updated_at'
            )
                ->orderBy('role.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Role::select(
                    'role.id',
                    'role.rolename',
                    'role.modifiedby',
                    'role.created_at',
                    'role.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('role.id', $params['sortOrder']);
            } else {
                $query = Role::select(
                    'role.id',
                    'role.rolename',
                    'role.modifiedby',
                    'role.created_at',
                    'role.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('role.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {

                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                default:

                    break;
            }


            $totalRows = count($query->get());

            $totalPages = ceil($totalRows / $params['limit']);
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $roles = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        return response([
            'status' => true,
            'data' => $roles,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRoleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRoleRequest $request)
    {
        DB::beginTransaction();
        try {
            $role = new Role();
            $role->rolename = $request->rolename;
            $role->modifiedby = $request->modifiedby;

            if ($role->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($role->getTable()),
                    'postingdari' => 'ENTRY ROLE',
                    'idtrans' => $role->id,
                    'nobuktitrans' => $role->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $role->toArray(),
                    'modifiedby' => $role->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($role->id, $request, $del);
            $role->position = $data->row;
            // dd($role->position );
            if (isset($request->limit)) {
                $role->page = ceil($role->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $role
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        return response([
            'status' => true,
            'data' => $role
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRoleRequest  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        DB::beginTransaction();
        try {
            $role->rolename = $request->rolename;
            $role->modifiedby = $request->modifiedby;

            if ($role->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($role->getTable()),
                    'postingdari' => 'EDIT ROLE',
                    'idtrans' => $role->id,
                    'nobuktitrans' => $role->id,
                    'aksi' => 'EDIT',
                    'datajson' => $role->toArray(),
                    'modifiedby' => $role->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $role->position = $this->getid($role->id, $request, 0)->row;


            if (isset($request->limit)) {
                $role->page = ceil($role->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $role
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role, Request $request)
    {
        DB::beginTransaction();
        try {
            if ($role->delete()) {
                $logTrail = [
                    'namatabel' => strtoupper($role->getTable()),
                    'postingdari' => 'DELETE ROLE',
                    'idtrans' => $role->id,
                    'nobuktitrans' => $role->id,
                    'aksi' => 'DELETE',
                    'datajson' => $role->toArray(),
                    'modifiedby' => $role->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            $del = 1;
            $data = $this->getid($role->id, $request, $del);
            $role->position = $data->row;
            $role->id = $data->id;

            if (isset($request->limit)) {
                $role->page = ceil($role->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $role
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('role')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getid($id, $request, $del)
    {

        $params = [
            'indexRow' => $request->indexRow ?? 1,
            'limit' => $request->limit ?? 100,
            'page' => $request->page ?? 1,
            'sortname' => $request->sortname ?? 'id',
            'sortorder' => $request->sortorder ?? 'asc',
        ];

        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('rolename', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($params['sortname'] == 'id') {
            $query = Role::select(
                'role.id as id_',
                'role.rolename',
                'role.modifiedby',
                'role.created_at',
                'role.updated_at'
            )
                ->orderBy('role.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Role::select(
                    'role.id as id_',
                    'role.rolename',
                    'role.modifiedby',
                    'role.created_at',
                    'role.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('role.id', $params['sortorder']);
            } else {
                $query = Role::select(
                    'role.id as id_',
                    'role.rolename',
                    'role.modifiedby',
                    'role.created_at',
                    'role.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])

                    ->orderBy('role.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'rolename',  'modifiedby', 'created_at', 'updated_at'], $query);


        if ($del == 1) {
            if ($request->page == 1) {
                $baris = $request->indexRow + 1;
            } else {
                $hal = $request->page - 1;
                $bar = $hal * $request->limit;
                $baris = $request->indexRow + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select('id as row')
                ->where('id_', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();
        return $data;
    }

    public function getroleid(Request $request)
    {

        $params = [
            'rolename' => $request->rolename ?? '',
        ];

        $query = Role::select('id')
            ->where('rolename', "=", $params['rolename']);

        $data = $query->first();

        return response([
            'data' => $data
        ]);
    }
}
