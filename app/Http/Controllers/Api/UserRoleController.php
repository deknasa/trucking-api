<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRoleRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Requests\DestroyUserRoleRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\UserRole;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\Role;
use App\Models\User;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Requests\StoreAclRequest;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Role $role): JsonResponse
    {
        $userRole = new UserRole();

        return response()->json([
            'data' => $userRole->get($role->acls()),
            'attributes' => [
                'totalRows' => $userRole->totalRows,
                'totalPages' => $userRole->totalPages
            ]
        ]);
    }

    public function detail()
    {
        $user = User::findOrFail(request()->user_id);

        return response([
            'data' => $user->roles
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreAclRequest $request, Role $role): JsonResponse
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
    public function show(UserRole $userrole)
    {
        $data = User::select('user')
            ->where('id', '=',  $userrole['user_id'])
            ->first();
        $userrole['user'] = $data['user'];

        return response([
            'status' => true,
            'data' => $userrole
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateUserRoleRequest $request, UserRole $userrole)
    {
        DB::beginTransaction();
        try {
            UserRole::where('user_id', $request->user_id)->delete();

            for ($i = 0; $i < count($request->role_id); $i++) {
                if ($request->status[$i] == 1) {
                    $userrole = new UserRole();
                    $userrole->user_id = $request->user_id;
                    $userrole->modifiedby = auth('api')->user()->name;
                    $userrole->role_id = $request->role_id[$i]  ?? 0;

                    if ($userrole->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($userrole->getTable()),
                            'postingdari' => 'EDIT USER ROLE',
                            'idtrans' => $userrole->id,
                            'nobuktitrans' => $userrole->id,
                            'aksi' => 'EDIT',
                            'datajson' => $userrole->toArray(),
                            'modifiedby' => $userrole->modifiedby
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                        DB::commit();
                    }
                }
            }

            /* Set position and page */
            $selected = $this->getPosition($userrole, $userrole->getTable());
            $userrole->position = $selected->position;
            $userrole->page = ceil($userrole->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $userrole
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy($id,  Request $request)
    {
        DB::beginTransaction();

        try {
            $userRole = UserRole::where('id', $id)->first();
            $delete = UserRole::where('id', $id)->delete();

            if ($delete > 0) {
                $logTrail = [
                    'namatabel' => strtoupper($userRole->getTable()),
                    'postingdari' => 'DELETE USERROLE',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
                    'aksi' => 'DELETE',
                    'datajson' => $userRole->toArray(),
                    'modifiedby' => $userRole->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($userRole, $userRole->getTable(), true);
                $userRole->position = $selected->position;
                $userRole->id = $selected->id;
                $userRole->page = ceil($userRole->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $userRole
                ]);
            } else {
                dd($delete);
                DB::rollBack();

                return response([
                    'status' => false,
                    'message' => 'Gagal dihapus'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    // public function destroy(UserRole $userrole, DestroyUserRoleRequest $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $delete = UserRole::where('user_id', $request->user_id)->delete();

    //         if ($delete > 0) {
    //             $logTrail = [
    //                 'namatabel' => strtoupper($userrole->getTable()),
    //                 'postingdari' => 'DELETE USER ROLE',
    //                 'idtrans' => $userrole->id,
    //                 'nobuktitrans' => $userrole->id,
    //                 'aksi' => 'DELETE',
    //                 'datajson' => $userrole->toArray(),
    //                 'modifiedby' => $userrole->modifiedby
    //             ];

    //             $validatedLogTrail = new StoreLogTrailRequest($logTrail);
    //             $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

    //             DB::commit();
    //         }


    //         // $userrole->position = $data->row;
    //         // $userrole->id = $data->id;
    //         // if (isset($request->limit)) {
    //         //     $userrole->page = ceil($userrole->position / $request->limit);
    //         // }

    //         $del = 1;

    //         $data = $this->getid($request->user_id, $request, $del);
    //         $selected = $this->getPosition($userrole, $userrole->getTable(), true);
    //         $userrole->position = $selected->position;
    //         $userrole->id = $selected->id;
    //         $userrole->page = ceil($userrole->position / ($request->limit ?? 10));

    //         return response([
    //             'status' => true,
    //             'message' => 'Berhasil dihapus',
    //             'data' => $userrole
    //         ]);
    //     } catch (\Throwable $th) {
    //         throw $th;
    //     }
    // }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('userrole')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName
     */
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $useracls = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'User',
                'index' => 'user',
            ],
            [
                'label' => 'Nama User',
                'index' => 'name',
            ],
        ];

        $this->toExcel('User Role', $useracls, $columns);
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
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('id_')->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('user_id');
        });

        $query = UserRole::select(
            DB::raw("userrole.user_id as user_id,
                        min(userrole.id) as id_,
                        max(userrole.modifiedby) as modifiedby,
                        max(userrole.created_at) as created_at,
                            max(userrole.updated_at) as updated_at")
        )
            ->Join('user', 'userrole.user_id', '=', 'user.id')
            ->groupby('userrole.user_id');


        DB::table($temp)->insertUsing(['user_id', 'id_', 'modifiedby', 'created_at', 'updated_at'], $query);



        /* Sorting */
        if ($params['sortname'] == 'user') {
            $query = DB::table($temp)
                ->select(
                    $temp . '.user_id as user_id',
                    $temp . '.id_ as id',
                    'user.user as user',
                    'user.name as name',
                    $temp . '.modifiedby as modifiedby',
                    $temp . '.updated_at as updated_at'
                )
                ->Join('user', 'user.id', '=', $temp . '.user_id')
                ->orderBy('user.name',  $params['sortorder']);
        } else {
            $query = DB::table($temp)
                ->select(
                    $temp . '.user_id as user_id',
                    $temp . '.id_ as id',
                    'user.user as user',
                    'user.name as name',
                    $temp . '.modifiedby as modifiedby',
                    $temp . '.updated_at as updated_at'
                )
                ->Join('user', 'user.id', '=', $temp . '.user_id')
                ->orderBy($temp . '.' . $params['sortname'],  $params['sortorder']);
        }
        // 
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('id_')->nullable();
            $table->string('user', 300)->nullable();
            $table->string('name', 300)->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('user_id');
        });







        DB::table($temp)->insertUsing(['user_id', 'id_',  'user', 'name',  'modifiedby', 'updated_at'], $query);


        if ($del == 1) {

            if ($params['page'] == 1) {
                $baris = $params['indexRow'] + 1;
            } else {
                $hal = $params['page'] - 1;
                $bar = $hal * $params['limit'];
                $baris = $params['indexRow'] + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id_ as row', 'user_id as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id_ as row', 'user_id as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select(
                    'id_ as row',
                    'user_id as id',
                )
                ->where('user_id', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();

        return $data;
    }

    public function detaillist(Request $request)
    {

        $param1 = $request->user_id;

        $controller = new ParameterController;
        $dataaktif = $controller->getparameterid('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF');
        $datanonaktif = $controller->getparameterid('STATUS AKTIF', 'STATUS AKTIF', 'NON AKTIF');
        $aktif = $dataaktif->id;
        $nonaktif = $datanonaktif->id;


        $data = Role::select(
            DB::raw("role.id as role_id,
                    role.rolename as rolename,
                    (case when isnull(userrole.role_id,0)=0 then 
                    " . DB::raw($nonaktif) . " 
                    else 
                    " . DB::raw($aktif) . " 
                    end) as status
            ")
        )
            ->leftJoin('userrole', function ($join)  use ($param1) {
                $join->on('role.id', '=', 'userrole.role_id');
                $join->on('userrole.user_id', '=', DB::raw("'" . $param1 . "'"));
            })
            ->orderBy('role.id')
            ->get();

        return response([
            'data' => $data
        ]);
    }


    public function combostatus(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }
}
