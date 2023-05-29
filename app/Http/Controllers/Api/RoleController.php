<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\DestroyRoleRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAclRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $role = new Role();

        return response([
            'data' => $role->get(),
            'attributes' => [
                'totalRows' => $role->totalRows,
                'totalPages' => $role->totalPages
            ]
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

            TOP:
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
            $selected = $this->getPosition($role, $role->getTable());
            $role->position = $selected->position;
            $role->page = ceil($role->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $role
            ], 201);
        } catch (QueryException $queryException) {
            if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                // Check if deadlock
                if ($queryException->errorInfo[1] === 1205) {
                    goto TOP;
                }
            }

            throw $queryException;
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
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
            $selected = $this->getPosition($role, $role->getTable());
            $role->position = $selected->position;
            $role->page = ceil($role->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $role
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroyRoleRequest $request, $id)
    {
        DB::beginTransaction();

        $role = new Role();
        $role = $role->lockAndDestroy($id);
        if ($role) {
            $logTrail = [
                'namatabel' => strtoupper($role->getTable()),
                'postingdari' => 'DELETE ROLE',
                'idtrans' => $role->id,
                'nobuktitrans' => $role->id,
                'aksi' => 'DELETE',
                'datajson' => $role->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($role, $role->getTable(), true);
            $role->position = $selected->position;
            $role->id = $selected->id;
            $role->page = ceil($role->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $role
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    
    public function getAcls(Role $role): JsonResponse
    {
        return response()->json([
            'role' => $role->rolename,
            'data' => $role->acls
        ]);
    }
    public function storeAcls(StoreAclRequest $request, Role $role): JsonResponse
    {
        DB::beginTransaction();

        try {
            $role->acls()->detach();

            foreach ($request->aco_ids as $aco_id) {
                $role->acls()->attach($aco_id, [
                    'modifiedby' => auth('api')->user()->name
                ]);
            }

            $logTrail = [
                'namatabel' => strtoupper($role->getTable()),
                'postingdari' => 'ENTRY ROLE ACL',
                'idtrans' => $role->id,
                'nobuktitrans' => $role->id,
                'aksi' => 'ENTRY',
                'datajson' => $role->load('acls')->toArray(),
                'modifiedby' => $role->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'user' => $role->load('acls')
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
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
