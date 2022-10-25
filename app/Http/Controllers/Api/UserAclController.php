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
    /**
     * @ClassName 
     */
    public function index()
    {
        // $params = [
        //     'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
        //     'limit' => request()->limit ?? 10,
        //     'filters' => json_decode(request()->filters, true) ?? [],
        //     'sortIndex' => request()->sortIndex ?? 'id',
        //     'sortOrder' => request()->sortOrder ?? 'asc',
        // ];

        // $temp = '##temp' . rand(1, 10000);
        // Schema::create($temp, function ($table) {
        //     $table->id();
        //     $table->bigInteger('user_id')->default('0');
        //     $table->bigInteger('id_')->default('0');
        //     $table->string('modifiedby', 30)->default('');
        //     $table->dateTime('created_at')->default('1900/1/1');
        //     $table->dateTime('updated_at')->default('1900/1/1');

        //     $table->index('user_id');
        // });

        // $query = DB::table((new UserAcl)->getTable())->select(
        //     DB::raw("useracl.user_id as user_id,
        //                 min(useracl.id) as id_,
        //                 max(useracl.modifiedby) as modifiedby,
        //                 max(useracl.created_at) as created_at,
        //                     max(useracl.updated_at) as updated_at")
        // )
        //     ->Join('user', 'useracl.user_id', '=', 'user.id')
        //     ->groupby('useracl.user_id');


        // DB::table($temp)->insertUsing(['user_id', 'id_', 'modifiedby', 'created_at', 'updated_at'], $query);

        // $totalRows = DB::table($temp)->count();
        // $totalPages = ceil($totalRows / $params['limit']);

        // /* Sorting */
        // if ($params['sortIndex'] == 'user') {
        //     $query = DB::table($temp)
        //         ->select(
        //             $temp . '.user_id as user_id',
        //             $temp . '.id_ as id',
        //             'user.user as user',
        //             $temp . '.modifiedby as modifiedby',
        //             $temp . '.updated_at as updated_at'
        //         )
        //         ->Join('user', 'user.id', '=', $temp . '.user_id')
        //         ->orderBy('user.user', $params['sortOrder']);
        // } else {
        //     $query = DB::table($temp)
        //         ->select(
        //             $temp . '.user_id as user_id',
        //             $temp . '.id_ as id',
        //             'user.user as user',
        //             $temp . '.modifiedby as modifiedby',
        //             $temp . '.updated_at as updated_at'
        //         )
        //         ->Join('user', 'user.id', '=', $temp . '.user_id')
        //         ->orderBy($temp . '.' . $params['sortIndex'], $params['sortOrder']);
        // }


        // /* Searching */
        // if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
        //     switch ($params['filters']['groupOp']) {
        //         case "AND":
        //             foreach ($params['filters']['rules'] as $index => $filters) {
        //                 if ($filters['field'] == 'user') {
        //                     $query = $query->where('user.user', 'LIKE', "%$filters[data]%");
        //                 } else {
        //                     $query = $query->where('user.' . $filters['field'], 'LIKE', "%$filters[data]%");
        //                 }
        //             }

        //             break;
        //         case "OR":
        //             foreach ($params['filters']['rules'] as $index => $filters) {
        //                 if ($filters['field'] == 'user') {
        //                     $query = $query->orWhere('user.user', 'LIKE', "%$filters[data]%");
        //                 } else {
        //                     $query = $query->orWhere('user.' . $filters['field'], 'LIKE', "%$filters[data]%");
        //                 }
        //             }

        //             break;
        //         default:

        //             break;
        //     }



        //     $totalRows = count($query->get());

        //     $totalPages = ceil($totalRows / $params['limit']);
        // }

        // /* Paging */
        // $query = $query->skip($params['offset'])
        //     ->take($params['limit']);

        // $useracl = $query->get();

        // /* Set attributes */
        // $attributes = [
        //     'totalRows' => $totalRows,
        //     'totalPages' => $totalPages
        // ];

        // return response([
        //     'status' => true,
        //     'data' => $useracl,
        //     'attributes' => $attributes,
        //     'params' => $params
        // ]);

        $userAcl = new UserAcl();

        return response([
            'data' => $userAcl->get(),
            'attributes' => [
                'totalRows' => $userAcl->totalRows,
                'totalPages' => $userAcl->totalPages
            ]
        ]);
    }

    public function detail()
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        $totalRows = DB::table((new UserAcl)->getTable())->count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new UserAcl)->getTable())->select(
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
                ->where('useracl.user_id', '=', request()->user_id)
                ->orderBy('useracl.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new UserAcl)->getTable())->select(
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
                    ->where('useracl.user_id', '=', request()->user_id)
                    ->orderBy('useracl.id', $params['sortOrder']);
            } else {
                $query = DB::table((new UserAcl)->getTable())->select(
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
                    ->where('useracl.user_id', '=', request()->user_id)
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('useracl.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'nama') {
                            $query = $query->where('acos.nama', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'class') {
                            $query = $query->where('acos.class', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'user') {
                            $query = $query->where('user.user', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'nama') {
                            $query = $query->orWhere('acos.nama', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'class') {
                            $query = $query->orWhere('acos.class', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'user') {
                            $query = $query->orWhere('user.user', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($filters['field'], 'LIKE', "%$filters[data]%");
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
    /**
     * @ClassName 
     */
    public function store(StoreUserAclRequest $request)
    {
        $request->validate([
            'user_id' => [
                'required',
                new NotExistsRule()
            ]
        ]);

        DB::beginTransaction();

        try {
            $controller = new ParameterController;
            $dataaktif = $controller->getparameterid('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF');
            $aktif = $dataaktif->id;

            for ($i = 0; $i < count($request->aco_id); $i++) {
                if ($request->status[$i] == $aktif) {
                    $useracl = new UserAcl();
                    $useracl->user_id = $request->user_id;
                    $useracl->modifiedby = auth('api')->user()->name;

                    $useracl->aco_id = $request->aco_id[$i]  ?? 0;

                    if ($useracl->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($useracl->getTable()),
                            'postingdari' => 'ENTRY USER ACL',
                            'idtrans' => $useracl->id,
                            'nobuktitrans' => $useracl->id,
                            'aksi' => 'ENTRY',
                            'datajson' => $useracl->toArray(),
                            'modifiedby' => $useracl->modifiedby
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                        DB::commit();
                    }
                }
            }

            /* Set position and page */
            $selected = $this->getPosition($useracl, $useracl->getTable());
            $useracl->position = $selected->position;
            $useracl->page = ceil($useracl->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => []
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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
    /**
     * @ClassName 
     */
    public function update(UpdateUserAclRequest $request, UserAcl $useracl)
    {
        DB::beginTransaction();
        try {
            UserAcl::where('user_id', $request->user_id)->delete();

            for ($i = 0; $i < count($request->aco_id); $i++) {
                if ($request->status[$i] == 1) {
                    $useracl = new UserAcl();
                    $useracl->user_id = $request->user_id;
                    $useracl->modifiedby = auth('api')->user()->name;
                    $useracl->aco_id = $request->aco_id[$i]  ?? 0;

                    if ($useracl->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($useracl->getTable()),
                            'postingdari' => 'ENTRY USER ACL',
                            'idtrans' => $useracl->id,
                            'nobuktitrans' => $useracl->id,
                            'aksi' => 'ENTRY',
                            'datajson' => $useracl->toArray(),
                            'modifiedby' => $useracl->modifiedby
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                        DB::commit();
                    }
                }
            }

            // /* Set position and page */
            // $del = 0;
            // $data = $this->getid($request->user_id, $request, $del);
            // $useracl->position = $data->id;
            // $useracl->id = $data->row;

            // if (isset($request->limit)) {
            //     $useracl->page = ceil($useracl->position / $request->limit);
            // }

            /* Set position and page */
            $selected = $this->getPosition($useracl, $useracl->getTable());
            $useracl->position = $selected->position;
            $useracl->page = ceil($useracl->position / ($request->limit ?? 10));


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
    /**
     * @ClassName 
     */
    public function destroy(UserAcl $useracl, DestroyUserAclRequest $request)
    {
        DB::beginTransaction();
        try {
            $delete = UserAcl::where('user_id', $request->user_id)->delete();


            if ($delete > 0) {
                $logTrail = [
                    'namatabel' => strtoupper($useracl->getTable()),
                    'postingdari' => 'ENTRY USER ACL',
                    'idtrans' => $useracl->id,
                    'nobuktitrans' => $useracl->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $useracl->toArray(),
                    'modifiedby' => $useracl->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            // $useracl->position = $data->row;
            // $useracl->id = $data->id;
            // if (isset($request->limit)) {
            //     $useracl->page = ceil($useracl->position / $request->limit);
            // }

            $del = 1;
            $data = $this->getid($request->user_id, $request, $del);
            /* Set position and page */
            $selected = $this->getPosition($useracl, $useracl->getTable(), true);
            $useracl->position = $selected->position;
            $useracl->id = $selected->id;
            $useracl->page = ceil($useracl->position / ($request->limit ?? 10));

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
                'label' => 'User ID',
                'index' => 'user_id',
            ],
            [
                'label' => 'User',
                'index' => 'user',
            ],
        ];

        $this->toExcel('User Acl', $useracls, $columns);
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
