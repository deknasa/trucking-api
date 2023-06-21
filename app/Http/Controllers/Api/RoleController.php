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
use App\Http\Requests\RangeExportReportRequest;
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
            $data = [
                'rolename' => $request->rolename
            ];
            $role = (new Role())->processStore($data);
            $role->position = $this->getPosition($role, $role->getTable())->position;
            $role->page = ceil($role->position / ($request->limit ?? 10));
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $role
            ], 201);
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
            $data = [
                'rolename' => $request->rolename
            ];

            $role = (new Role())->processUpdate($role, $data);
            $role->position = $this->getPosition($role, $role->getTable())->position;
            $role->page = ceil($role->position / ($request->limit ?? 10));

            DB::commit();

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

        try {
            $role = (new Role())->processDestroy($id);
            $selected = $this->getPosition($role, $role->getTable(), true);
            $role->position = $selected->position;
            $role->id = $selected->id;
            $role->page = ceil($role->position / ($request->limit ?? 10));
       
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $role
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
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

    public function export(RangeExportReportRequest $request)
    {
        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
        } else {
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $roles = $decodedResponse['data'];

            $judulLaporan = $roles[0]['judulLaporan'];

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Role Name',
                    'index' => 'rolename',
                ],
            ];

            $this->toExcel($judulLaporan, $roles, $columns);
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
