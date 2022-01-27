<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Models\LogTrail;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\UserRoleRequest;
use Illuminate\Support\Facades\Schema;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('user_id');
        });

        $query = UserRole::select(
            DB::raw("userrole.user_id as user_id,
                            max(userrole.modifiedby) as modifiedby,
                            max(userrole.created_at) as created_at,
                            max(userrole.updated_at) as updated_at")
        )
            ->Join('user', 'userrole.user_id', '=', 'user.id')
            ->groupby('userrole.user_id');


        DB::table($temp)->insertUsing(['user_id', 'modifiedby', 'created_at', 'updated_at'], $query);

        $totalRows = DB::table($temp)
            ->count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'name') {
            $query = DB::table($temp)
                ->select(
                    $temp . '.user_id as user_id',
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
                        if ($search['field'] == 'name') {
                            $query = $query->where('user.name', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'name') {
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
                'user.name as user_id',
                'role.rolename as role_id',
                'userrole.modifiedby',
                'userrole.created_at',
                'userrole.updated_at'
            )
                ->Join('user', 'userrole.user_id', '=', 'user.id')
                ->Join('role', 'userrole.role_id', '=', 'role.id')
                ->where('userrole.user_id','=',$request->user_id)
                ->orderBy('userrole.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = UserRole::select(
                    'userrole.id',
                    'user.name as user_id',
                    'role.rolename as role_id',
                    'userrole.modifiedby',
                    'userrole.created_at',
                    'userrole.updated_at'
                )
                    ->Join('user', 'userrole.user_id', '=', 'user.id')
                    ->Join('role', 'userrole.role_id', '=', 'role.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->where('userrole.user_id','=',$request->user_id)
                    ->orderBy('userrole.id', $params['sortOrder']);
            } else {
                $query = UserRole::select(
                    'userrole.id',
                    'user.name as user_id',
                    'role.rolename as role_id',
                    'userrole.modifiedby',
                    'userrole.created_at',
                    'userrole.updated_at'
                )
                    ->Join('user', 'userrole.user_id', '=', 'user.id')
                    ->Join('role', 'userrole.role_id', '=', 'role.id')
                    ->where('userrole.user_id','=',$request->user_id)
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('userrole.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'user_id') {
                            $query = $query->where('user.name', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'role_id') {
                            $query = $query->where('role.rolename', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'user_id') {
                            $query = $query->orWhere('user.name', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'role_id') {
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
    public function store(UserRoleRequest $request)
    {
        DB::beginTransaction();
        try {
            $userrole = new UserRole();
            $userrole->user_id = $request->user_id;
            $userrole->role_id = $request->role_id;
            $userrole->save();

            $datajson = [
                'id' => $userrole->id,
                'user_id' => $request->user_id,
                'role_id' => $request->role_id
            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'USERROLE';
            $logtrail->postingdari = 'ENTRY USER ROLE';
            $logtrail->idtrans = $userrole->id;
            $logtrail->nobuktitrans = $userrole->id;
            $logtrail->aksi = 'ENTRY';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($userrole->id, $request, $del);
            $userrole->position = $data->row;
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
    public function update(UserRoleRequest $request, UserRole $userrole)
    {
        DB::beginTransaction();
        try {
            $userrole->update(array_map('strtoupper', $request->validated()));

            $datajson = [
                'id' => $userrole->id,
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
                'statusaktif' => $request->statusaktif,
            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'CABANG';
            $logtrail->postingdari = 'EDIT CABANG';
            $logtrail->idtrans = $userrole->id;
            $logtrail->nobuktitrans = $userrole->id;
            $logtrail->aksi = 'EDIT';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();
            DB::commit();

            /* Set position and page */
            $userrole->position = userrole::orderBy($request->sortname ?? 'id', $request->sortorder ?? 'asc')
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $userrole->{$request->sortname})
                ->where('id', '<=', $userrole->id)
                ->count();

            if (isset($request->limit)) {
                $userrole->page = ceil($userrole->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
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
    public function destroy(UserRole $userrole, UserRoleRequest $request)
    {
        DB::beginTransaction();
        try {

            UserRole::destroy($userrole->id);

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'USERROLE';
            $logtrail->postingdari = 'DELETE USER ROLE';
            $logtrail->idtrans = $userrole->id;
            $logtrail->nobuktitrans = $userrole->id;
            $logtrail->aksi = 'DELETE';
            $logtrail->datajson = '';

            $logtrail->save();

            DB::commit();
            UserRole::destroy($userrole->id);
            $del = 1;
            $data = $this->getid($userrole->id, $request, $del);
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

        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('user_id', 300)->default('');
            $table->string('role_id', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($request->sortname == 'id') {
            $query = UserRole::select(
                'userrole.id as id_',
                'user.name as user_id',
                'role.rolename as role_id',
                'userrole.modifiedby',
                'userrole.created_at',
                'userrole.updated_at'
            )
                ->leftJoin('user', 'userrole.user_id', '=', 'user.id')
                ->leftJoin('role', 'userrole.role_id', '=', 'role.id')
                ->orderBy('userrole.id', $request->sortorder);
        } else {
            if ($request->sortorder == 'asc') {
                $query = UserRole::select(
                    'userrole.id as id_',
                    'user.name as user_id',
                    'role.rolename as role_id',
                    'userrole.modifiedby',
                    'userrole.created_at',
                    'userrole.updated_at'
                )
                    ->leftJoin('user', 'userrole.user_id', '=', 'user.id')
                    ->leftJoin('role', 'userrole.role_id', '=', 'role.id')
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('userrole.id', $request->sortorder);
            } else {
                $query = UserRole::select(
                    'userrole.id as id_',
                    'user.name as user_id',
                    'role.rolename as role_id',
                    'userrole.modifiedby',
                    'userrole.created_at',
                    'userrole.updated_at'
                )
                    ->leftJoin('user', 'userrole.user_id', '=', 'user.id')
                    ->leftJoin('role', 'userrole.role_id', '=', 'role.id')
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('userrole.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'user_id', 'role_id',  'modifiedby', 'created_at', 'updated_at'], $query);


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

    public function detaillist(Request $request) {
        $data = UserRole::select(
            'userrole.id',
            'role.rolename as role_id',
        )
            ->Join('role', 'userrole.role_id', '=', 'role.id')
            ->where('userrole.user_id','=',$request->user_id)
            ->orderBy('userrole.id')
            ->get();

            return response([
                'data' => $data
            ]);
    }
}
