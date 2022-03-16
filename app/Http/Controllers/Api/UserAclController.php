<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserAclRequest;
use App\Http\Requests\UpdateUserAclRequest;
use App\Http\Requests\DestroyUserAclRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\UserAcl;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\User;
use App\Models\Acos;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use App\Rules\NotExistsRule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ParameterController;

class UserAclController extends Controller
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

        $query = UserAcl::select(
            DB::raw("useracl.user_id as user_id,
                        min(useracl.id) as id_,
                        max(useracl.modifiedby) as modifiedby,
                        max(useracl.created_at) as created_at,
                            max(useracl.updated_at) as updated_at")
        )
            ->Join('user', 'useracl.user_id', '=', 'user.id')
            ->groupby('useracl.user_id');


        DB::table($temp)->insertUsing(['user_id', 'id_', 'modifiedby', 'created_at', 'updated_at'], $query);

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
                    $temp . '.modifiedby as modifiedby',
                    $temp . '.updated_at as updated_at'
                )
                ->Join('user', 'user.id', '=', $temp . '.user_id')
                ->orderBy('user.user', $params['sortOrder']);
        } else {
            $query = DB::table($temp)
                ->select(
                    $temp . '.user_id as user_id',
                    $temp . '.id_ as id',
                    'user.user as user',
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
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'user') {
                            $query = $query->orWhere('user.user', 'LIKE', "%$search[data]%");
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

        $useracl = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $useracl,
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

        // dd($params);
        $totalRows = UserAcl::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = UserAcl::select(
                'useracl.id',
                'acos.nama as nama',
                'acos.class as class',
                'user.user as user',
                'useracl.modifiedby',
                'useracl.created_at',
                'useracl.updated_at'
            )
                ->Join('acos', 'useracl.aco_id', '=', 'acos.id')
                ->Join('user', 'useracl.user_id', '=', 'user.id')
                ->where('useracl.user_id', '=', $request->user_id)
                ->orderBy('useracl.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = UserAcl::select(
                    'useracl.id',
                    'acos.nama as nama',
                    'acos.class as class',
                    'user.user as user',
                    'useracl.modifiedby',
                    'useracl.created_at',
                    'useracl.updated_at'
                )
                    ->Join('acos', 'useracl.aco_id', '=', 'acos.id')
                    ->Join('user', 'useracl.user_id', '=', 'user.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->where('useracl.user_id', '=', $request->user_id)
                    ->orderBy('useracl.id', $params['sortOrder']);
            } else {
                $query = UserAcl::select(
                    'useracl.id',
                    'acos.nama as nama',
                    'acos.class as class',
                    'user.user as user',
                    'useracl.modifiedby',
                    'useracl.created_at',
                    'useracl.updated_at'
                )
                    ->Join('acos', 'useracl.aco_id', '=', 'acos.id')
                    ->Join('user', 'useracl.user_id', '=', 'user.id')
                    ->where('useracl.user_id', '=', $request->user_id)
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('useracl.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'nama') {
                            $query = $query->where('acos.nama', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'class') {
                            $query = $query->where('acos.class', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'user') {
                            $query = $query->where('user.user', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'nama') {
                            $query = $query->orWhere('acos.nama', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'class') {
                            $query = $query->orWhere('acos.class', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'user') {
                            $query = $query->orWhere('user.user', 'LIKE', "%$search[data]%");
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

        $useracl = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $useracl,
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
     * @param  \App\Http\Requests\StoreUserAclRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserAclRequest $request)
    {

        // $request->validate([
        //         'user_id' => 'required|unique:useracl,user_id'
        // ]);
        $request->validate([
            'user_id' => [
                'required',
                 New NotExistsRule()
            ]
        ]);

        DB::beginTransaction();
        try {
            $controller = new ParameterController;
            $dataaktif = $controller->getparameterid('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF');
            $aktif = $dataaktif->id;
            for ($i = 0; $i < count($request->aco_id); $i++) {
                $useracl = new UserAcl();

                $useracl->user_id = $request->user_id;
                $useracl->modifiedby = $request->modifiedby;
                $useracl->aco_id = $request->aco_id[$i]  ?? 0;
                if ($request->status[$i] == $aktif) {
                    // dd($request->user_id[$i]);
                    $useracl->save();
                }
            }


            $datajson = [
                'aco_id' => $request->aco_id,
                'user_id' => $request->user_id,
                'modifiedby' => strtoupper($request->modifiedby),

            ];

             

            $datalogtrail = [
                'namatabel' => 'USERACL',
                'postingdari' => 'ENTRY USER ACL',
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

            $useracl->position = $data->id;
            $useracl->id = $data->row;



            // dd($useracl->position );
            if (isset($request->limit)) {
                $useracl->page = ceil($useracl->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $useracl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserAcl  $userAcl
     * @return \Illuminate\Http\Response
     */
    public function show(UserAcl $useracl)
    {
        $data = User::select('user')
            ->where('id', '=',  $useracl['user_id'])
            ->first();
        $useracl['user'] = $data['user'];

        // dd($useracl);
        return response([
            'status' => true,
            'data' => $useracl
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserAcl  $userAcl
     * @return \Illuminate\Http\Response
     */
    public function edit(UserAcl $useracl)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserAclRequest  $request
     * @param  \App\Models\UserAcl  $userAcl
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserAclRequest $request, UserAcl $useracl)
    {
        DB::beginTransaction();
        try {
            UserAcl::where('user_id', $request->user_id)->delete();

            for ($i = 0; $i < count($request->aco_id); $i++) {
                $useracl = new UserAcl();
                $useracl->user_id = $request->user_id;
                $useracl->modifiedby = $request->modifiedby;
                $useracl->aco_id = $request->aco_id[$i]  ?? 0;
                $useracl->save();
            }



            $datajson = [
                'aco_id' => $request->aco_id,
                'user_id' => $request->user_id,
                'modifiedby' => strtoupper($request->modifiedby),

            ];

             

            $datalogtrail = [
                'namatabel' => 'USERACL',
                'postingdari' => 'EDIT USER ACL',
                'idtrans' => $useracl->id,
                'nobuktitrans' => $useracl->id,
                'aksi' => 'EDIT',
                'datajson' => json_encode($datajson),
                'modifiedby' => $useracl->modifiedby,
            ];

            $data=new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);   

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($request->user_id, $request, $del);
            $useracl->position = $data->id;
            $useracl->id = $data->row;
            // dd($useracl->position );
            if (isset($request->limit)) {
                $useracl->page = ceil($useracl->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $useracl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserAcl  $userAcl
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserAcl $useracl, DestroyUserAclRequest $request)
    {
        DB::beginTransaction();
        try {

            UserAcl::where('user_id', $request->user_id)->delete();

            $datajson = [
                'aco_id' => $request->aco_id,
                'user_id' => $request->user_id,
                'modifiedby' => strtoupper($request->modifiedby),

            ];

             

            $datalogtrail = [
                'namatabel' => 'USERACL',
                'postingdari' => 'DELETE USER ACL',
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

            $useracl->position = $data->row;
            $useracl->id = $data->id;
            if (isset($request->limit)) {
                $useracl->page = ceil($useracl->position / $request->limit);
            }
            // dd($useracl);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $useracl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('useracl')->getColumns();

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

        $query = UserAcl::select(
            DB::raw("useracl.user_id as user_id,
                        min(useracl.id) as id_,
                        max(useracl.modifiedby) as modifiedby,
                        max(useracl.created_at) as created_at,
                            max(useracl.updated_at) as updated_at")
        )
            ->Join('user', 'useracl.user_id', '=', 'user.id')
            ->groupby('useracl.user_id');


        DB::table($temp)->insertUsing(['user_id', 'id_', 'modifiedby', 'created_at', 'updated_at'], $query);



        /* Sorting */
        if ($params['sortname'] == 'user') {
            $query = DB::table($temp)
                ->select(
                    $temp . '.user_id as user_id',
                    $temp . '.id_ as id',
                    'user.user as user',
                    $temp . '.modifiedby as modifiedby',
                    $temp . '.updated_at as updated_at'
                )
                ->Join('user', 'user.id', '=', $temp . '.user_id')
                ->orderBy('user.user',  $params['sortorder']);
        } else {
            $query = DB::table($temp)
                ->select(
                    $temp . '.user_id as user_id',
                    $temp . '.id_ as id',
                    'user.user as user',
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
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('user_id');
        });







        DB::table($temp)->insertUsing(['user_id', 'id_',  'user',  'modifiedby', 'updated_at'], $query);


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


        $data = Acos::select(
            DB::raw("acos.id as aco_id,
            acos.nama as nama,
            acos.class as class,
            (case when isnull(useracl.user_id,0)=0 then 
                    " . DB::raw($nonaktif) . " 
                    else 
                    " . DB::raw($aktif) . " 
                    end) as status
            ")
        )
            ->leftJoin('useracl', function ($join)  use ($param1) {
                $join->on('acos.id', '=', 'useracl.aco_id');
                $join->on('useracl.user_id', '=', DB::raw("'" . $param1 . "'"));
            })
            ->orderBy('acos.id')
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
