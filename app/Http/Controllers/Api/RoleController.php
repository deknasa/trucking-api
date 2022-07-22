<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        $totalRows = DB::table((new Role)->getTable())->count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Role)->getTable())->select(
                'role.id',
                'role.rolename',
                'role.modifiedby',
                'role.created_at',
                'role.updated_at'
            )
                ->orderBy('role.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Role)->getTable())->select(
                    'role.id',
                    'role.rolename',
                    'role.modifiedby',
                    'role.created_at',
                    'role.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('role.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Role)->getTable())->select(
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
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {

                        $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($filters['field'], 'LIKE', "%$filters[data]%");
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
     * @ClassName 
     */
    public function store(StoreRoleRequest $request)
    {
        DB::beginTransaction();
        try {
            $role = new Role();
            $role->rolename = $request->rolename;
            $role->modifiedby = auth('api')->user()->name;

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
     * @ClassName 
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        DB::beginTransaction();
        try {
            $role->rolename = $request->rolename;
            $role->modifiedby = auth('api')->user()->name;

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
     * @ClassName 
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

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $roles = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Role Name',
                'index' => 'rolename',
            ],
        ];

        $this->toExcel('Role', $roles, $columns);
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
