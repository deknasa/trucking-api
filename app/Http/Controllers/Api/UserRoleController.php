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

class UserRoleController extends Controller
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

        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('user_id')->default('0');
            $table->bigInteger('id_')->default('0');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

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


        DB::table($temp)->insertUsing(['user_id','id_','modifiedby', 'created_at', 'updated_at'], $query);

        $totalRows = DB::table($temp)
            ->count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'user') {
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
                ->orderBy('user.name', $params['sortOrder']);
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
                ->orderBy($temp . '.' . $params['sortIndex'], $params['sortOrder']);
        }



        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'user') {
                            $query = $query->where('user.user', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'name') {
                            $query = $query->where('user.name', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'user') {
                            $query = $query->orWhere('user.user', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'name') {
                            $query = $query->orWhere('user.name', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
                        }
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

        $userroles = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $userroles,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function detail(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 100,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];


        $totalRows = UserRole::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = UserRole::select(
                'userrole.id',
                'user.user as user',
                'role.rolename as rolename',
                'userrole.modifiedby',
                'userrole.created_at',
                'userrole.updated_at'
            )
                ->Join('user', 'userrole.user_id', '=', 'user.id')
                ->Join('role', 'userrole.role_id', '=', 'role.id')
                ->where('userrole.user_id', '=', $request->user_id)
                ->orderBy('userrole.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = UserRole::select(
                    'userrole.id',
                    'user.user as user',
                    'role.rolename as rolename',
                    'userrole.modifiedby',
                    'userrole.created_at',
                    'userrole.updated_at'
                )
                    ->Join('user', 'userrole.user_id', '=', 'user.id')
                    ->Join('role', 'userrole.role_id', '=', 'role.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->where('userrole.user_id', '=', $request->user_id)
                    ->orderBy('userrole.id', $params['sortOrder']);
            } else {
                $query = UserRole::select(
                    'userrole.id',
                    'user.user as user',
                    'role.rolename as rolename',
                    'userrole.modifiedby',
                    'userrole.created_at',
                    'userrole.updated_at'
                )
                    ->Join('user', 'userrole.user_id', '=', 'user.id')
                    ->Join('role', 'userrole.role_id', '=', 'role.id')
                    ->where('userrole.user_id', '=', $request->user_id)
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('userrole.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'user') {
                            $query = $query->where('user.user', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'rolename') {
                            $query = $query->where('role.rolename', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'user') {
                            $query = $query->orWhere('user.user', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'rolename') {
                            $query = $query->orWhere('role.rolename', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
                        }
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

        $userroles = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $userroles,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserRoleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRoleRequest $request)
    {

        DB::beginTransaction();
        try {
            $controller = new ParameterController;
            $dataaktif = $controller->getparameterid('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF');
            $aktif = $dataaktif->id;
            // dd($aktif);
            for ($i = 0; $i < count($request->role_id); $i++) {
                $userrole = new UserRole();

                $userrole->user_id = $request->user_id;
                $userrole->modifiedby = $request->modifiedby;
                $userrole->role_id = $request->role_id[$i]  ?? 0;
                if ($request->status[$i] == $aktif) {
                    // dd($request->role_id[$i]);
                    $userrole->save();
                }
            }


            $datajson = [
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
                'modifiedby' => strtoupper($request->modifiedby),

            ];

            $datalogtrail = [
                'namatabel' => 'USERROLE',
                'postingdari' => 'ENTRY USER ROLE',
                'idtrans' => $request->id,
                'nobuktitrans' => $request->id,
                'aksi' => 'ENTRY',
                'datajson' => json_encode($datajson),
                'modifiedby' => $request->modifiedby,
            ];

            $data=new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($request->user_id, $request, $del) ?? 0;

            $userrole->position = $data->id;
            $userrole->id = $data->row;

            

            // dd($userrole->position );
            if (isset($request->limit)) {
                $userrole->page = ceil($userrole->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $userrole
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserRole  $userRole
     * @return \Illuminate\Http\Response
     */
    public function show(UserRole $userrole)
    {
        $data = User::select('user')
            ->where('id', '=',  $userrole['user_id'])
            ->first();
        $userrole['user'] = $data['user'];

        // dd($userrole);
        return response([
            'status' => true,
            'data' => $userrole
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserRole  $userRole
     * @return \Illuminate\Http\Response
     */
    public function edit(UserRole $userrole)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserRoleRequest  $request
     * @param  \App\Models\UserRole  $userRole
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRoleRequest $request, UserRole $userrole)
    {
        DB::beginTransaction();
        try {
            Userrole::where('user_id', $request->user_id)->delete();

            for ($i = 0; $i < count($request->role_id); $i++) {
                $userrole = new UserRole();
                $userrole->user_id = $request->user_id;
                $userrole->modifiedby = $request->modifiedby;
                $userrole->role_id = $request->role_id[$i]  ?? 0;
                $userrole->save();
            }



            $datajson = [
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
                'modifiedby' => strtoupper($request->modifiedby),

            ];

            $datalogtrail = [
                'namatabel' => 'USERROLE',
                'postingdari' => 'EDIT USER ROLE',
                'idtrans' => $request->id,
                'nobuktitrans' => $request->id,
                'aksi' => 'EDIT',
                'datajson' => json_encode($datajson),
                'modifiedby' => $request->modifiedby,
            ];

            $data=new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($request->user_id, $request, $del);
            $userrole->position = $data->id;
            $userrole->id = $data->row;
            // dd($userrole->position );
            if (isset($request->limit)) {
                $userrole->page = ceil($userrole->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $userrole
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserRole  $userRole
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserRole $userrole, DestroyUserRoleRequest $request)
    {
       
        DB::beginTransaction();
        try {

            Userrole::where('user_id', $request->user_id)->delete();

            $datajson = [
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
                'modifiedby' => strtoupper($request->modifiedby),

            ];

            $datalogtrail = [
                'namatabel' => 'USERROLE',
                'postingdari' => 'DELETE USER ROLE',
                'idtrans' => $request->id,
                'nobuktitrans' => $request->id,
                'aksi' => 'DELETE',
                'datajson' => json_encode($datajson),
                'modifiedby' => $request->modifiedby,
            ];

            $data=new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            $del = 1;
            // dd($request->user_id);

            $data = $this->getid($request->user_id, $request, $del);
            
            $userrole->position = $data->row;
            $userrole->id = $data->id;
            if (isset($request->limit)) {
                $userrole->page = ceil($userrole->position / $request->limit);
            }
            // dd($userrole);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $userrole
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

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
            $table->bigInteger('user_id')->default('0');
            $table->bigInteger('id_')->default('0');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

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


        DB::table($temp)->insertUsing(['user_id','id_','modifiedby', 'created_at', 'updated_at'], $query);

    

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
            $table->bigInteger('user_id')->default('0');
            $table->bigInteger('id_')->default('0');
            $table->string('user', 300)->default('');
            $table->string('name', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('user_id');
        });



      



        DB::table($temp)->insertUsing(['user_id', 'id_',  'user','name',  'modifiedby', 'updated_at'], $query);


        if ($del == 1) {
         
            if ($params['page'] == 1) {
                $baris = $params['indexRow'] + 1;
            } else {
                dd('test');
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
                $table->integer('id')->length(11)->default(0);
                $table->string('parameter', 50)->default(0);
                $table->string('param', 50)->default(0);
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
