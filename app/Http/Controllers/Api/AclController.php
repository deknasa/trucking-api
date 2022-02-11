<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StoreAclRequest;
use App\Http\Requests\UpdateAclRequest;
use App\Http\Requests\DestroyAclRequest;

use App\Models\Acl;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\Role;
use App\Models\Acos;

use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Controller;

class AclController extends Controller
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
            $table->bigInteger('role_id')->default('0');
            $table->bigInteger('id_')->default('0');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('role_id');
        });

        $query = Acl::select(
            DB::raw("acl.role_id as role_id,
                        min(acl.id) as id_,
                        max(acl.modifiedby) as modifiedby,
                        max(acl.created_at) as created_at,
                            max(acl.updated_at) as updated_at")
        )
            ->Join('role', 'acl.role_id', '=', 'role.id')
            ->groupby('acl.role_id');


        DB::table($temp)->insertUsing(['role_id', 'id_', 'modifiedby', 'created_at', 'updated_at'], $query);

        $totalRows = DB::table($temp)
            ->count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'rolename') {
            $query = DB::table($temp)
                ->select(
                    $temp . '.role_id as role_id',
                    $temp . '.id_ as id',
                    'role.rolename as rolename',
                    $temp . '.modifiedby as modifiedby',
                    $temp . '.updated_at as updated_at'
                )
                ->Join('role', 'role.id', '=', $temp . '.role_id')
                ->orderBy('role.rolename', $params['sortOrder']);
        } else {
            $query = DB::table($temp)
                ->select(
                    $temp . '.role_id as role_id',
                    $temp . '.id_ as id',
                    'role.rolename as rolename',
                    $temp . '.modifiedby as modifiedby',
                    $temp . '.updated_at as updated_at'
                )
                ->Join('role', 'role.id', '=', $temp . '.role_id')
                ->orderBy($temp . '.' . $params['sortIndex'], $params['sortOrder']);
        }



        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'rolename') {
                            $query = $query->where('role.rolename', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'rolename') {
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

        $acl = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $acl,
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
        $totalRows = Acl::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = Acl::select(
                'acl.id',
                'acos.nama as nama',
                'acos.class as class',
                'role.rolename as rolename',
                'acl.modifiedby',
                'acl.created_at',
                'acl.updated_at'
            )
                ->Join('acos', 'acl.aco_id', '=', 'acos.id')
                ->Join('role', 'acl.role_id', '=', 'role.id')
                ->where('acl.role_id', '=', $request->role_id)
                ->orderBy('acl.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Acl::select(
                    'acl.id',
                    'acos.nama as nama',
                    'acos.class as class',
                    'role.rolename as rolename',
                    'acl.modifiedby',
                    'acl.created_at',
                    'acl.updated_at'
                )
                    ->Join('acos', 'acl.aco_id', '=', 'acos.id')
                    ->Join('role', 'acl.role_id', '=', 'role.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->where('acl.role_id', '=', $request->role_id)
                    ->orderBy('acl.id', $params['sortOrder']);
            } else {
                $query = Acl::select(
                    'acl.id',
                    'acos.nama as nama',
                    'acos.class as class',
                    'role.rolename as rolename',
                    'acl.modifiedby',
                    'acl.created_at',
                    'acl.updated_at'
                )
                    ->Join('acos', 'acl.aco_id', '=', 'acos.id')
                    ->Join('role', 'acl.role_id', '=', 'role.id')
                    ->where('acl.role_id', '=', $request->role_id)
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('acl.id', 'asc');
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
                        } else if ($search['field'] == 'rolename') {
                            $query = $query->where('role.rolename', 'LIKE', "%$search[data]%");
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

        $acl = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $acl,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Il\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreaclRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAclRequest $request)
    {
        DB::beginTransaction();
        try {
            $controller = new ParameterController;
            $dataaktif = $controller->getparameterid('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF');
            $aktif = $dataaktif->id;
            for ($i = 0; $i < count($request->aco_id); $i++) {
                $acl = new Acl();

                $acl->role_id = $request->role_id;
                $acl->modifiedby = $request->modifiedby;
                $acl->aco_id = $request->aco_id[$i]  ?? 0;
                if ($request->status[$i] == $aktif) {
                    // dd($request->role_id[$i]);
                    $acl->save();
                }
            }




            $datajson = [
                'aco_id' => $request->aco_id,
                'role_id' => $request->role_id,
                'modifiedby' => strtoupper($request->modifiedby),

            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'ACL';
            $logtrail->postingdari = 'ENTRY ACL';
            $logtrail->idtrans = $request->role_id;
            $logtrail->nobuktitrans = $request->role_id;
            $logtrail->aksi = 'ENTRY';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($request->role_id, $request, $del) ?? 0;

            $acl->position = $data->id;
            $acl->id = $data->row;



            // dd($acl->position );
            if (isset($request->limit)) {
                $acl->page = ceil($acl->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $acl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\acl  $acl
     * @return \Illuminate\Http\Response
     */
    public function show(Acl $acl)
    {
        $data = Role::select('rolename')
            ->where('id', '=',  $acl['role_id'])
            ->first();
        $acl['rolename'] = $data['rolename'];

        // dd($acl);
        return response([
            'status' => true,
            'data' => $acl
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\acl  $acl
     * @return \Illuminate\Http\Response
     */
    public function edit(Acl $acl)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateaclRequest  $request
     * @param  \App\Models\acl  $acl
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAclRequest $request, acl $acl)
    {
        DB::beginTransaction();
        try {
            Acl::where('role_id', $request->role_id)->delete();

            for ($i = 0; $i < count($request->aco_id); $i++) {
                $acl = new Acl();
                $acl->role_id = $request->role_id;
                $acl->modifiedby = $request->modifiedby;
                $acl->aco_id = $request->aco_id[$i]  ?? 0;
                $acl->save();
            }



            $datajson = [
                'id' => $acl->id,
                'aco_id' => $request->aco_id,
                'role_id' => $request->role_id,
                'modifiedby' => strtoupper($request->modifiedby),

            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'ACL';
            $logtrail->postingdari = 'ENTRY ACL';
            $logtrail->idtrans = $acl->id;
            $logtrail->nobuktitrans = $acl->id;
            $logtrail->aksi = 'ENTRY';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($request->role_id, $request, $del);
            $acl->position = $data->id;
            $acl->id = $data->row;
            // dd($acl->position );
            if (isset($request->limit)) {
                $acl->page = ceil($acl->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $acl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\acl  $acl
     * @return \Illuminate\Http\Response
     */
    public function destroy(Acl $acl, Request $request)
    {
        DB::beginTransaction();
        try {

            Acl::where('role_id', $request->role_id)->delete();

            $datajson = [
                'id' => $acl->id,
                'modifiedby' => strtoupper($request->modifiedby),
            ];

            $logtrail = new LogTrail();
            $logtrail->namatabel = 'ACL';
            $logtrail->postingdari = 'DELETE ACL';
            $logtrail->idtrans = $acl->id;
            $logtrail->nobuktitrans = $acl->id;
            $logtrail->aksi = 'DELETE';
            $logtrail->datajson = json_encode($datajson);

            $logtrail->save();

            DB::commit();
            $del = 1;
            // dd($request->user_id);

            $data = $this->getid($request->role_id, $request, $del);

            $acl->position = $data->row;
            $acl->id = $data->id;
            if (isset($request->limit)) {
                $acl->page = ceil($acl->position / $request->limit);
            }
            // dd($acl);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $acl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('acl')->getColumns();

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
            $table->bigInteger('role_id')->default('0');
            $table->bigInteger('id_')->default('0');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('role_id');
        });

        $query = Acl::select(
            DB::raw("acl.role_id as role_id,
                        min(acl.id) as id_,
                        max(acl.modifiedby) as modifiedby,
                        max(acl.created_at) as created_at,
                            max(acl.updated_at) as updated_at")
        )
            ->Join('role', 'acl.role_id', '=', 'role.id')
            ->groupby('acl.role_id');


        DB::table($temp)->insertUsing(['role_id', 'id_', 'modifiedby', 'created_at', 'updated_at'], $query);



        /* Sorting */
        if ($request->sortname == 'rolename') {
            $query = DB::table($temp)
                ->select(
                    $temp . '.role_id as role_id',
                    $temp . '.id_ as id',
                    'role.rolename as rolename',
                    $temp . '.modifiedby as modifiedby',
                    $temp . '.updated_at as updated_at'
                )
                ->Join('role', 'role.id', '=', $temp . '.role_id')
                ->orderBy('role.rolename',  $request->sortorder);
        } else {
            $query = DB::table($temp)
                ->select(
                    $temp . '.role_id as role_id',
                    $temp . '.id_ as id',
                    'role.rolename as rolename',
                    $temp . '.modifiedby as modifiedby',
                    $temp . '.updated_at as updated_at'
                )
                ->Join('role', 'role.id', '=', $temp . '.role_id')
                ->orderBy($temp . '.' . $request->sortname,  $request->sortorder);
        }
        // 
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('role_id')->default('0');
            $table->bigInteger('id_')->default('0');
            $table->string('rolename', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('role_id');
        });







        DB::table($temp)->insertUsing(['role_id', 'id_',  'rolename',  'modifiedby', 'updated_at'], $query);


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
                    ->select('id_ as row', 'role_id as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id_ as row', 'role_id as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select(
                    'id_ as row',
                    'role_id as id',
                )
                ->where('role_id', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();

        return $data;
    }

    public function detaillist(Request $request)
    {

        $param1 = $request->role_id;

        $controller = new ParameterController;
        $dataaktif = $controller->getparameterid('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF');
        $datanonaktif = $controller->getparameterid('STATUS AKTIF', 'STATUS AKTIF', 'NON AKTIF');
        $aktif = $dataaktif->id;
        $nonaktif = $datanonaktif->id;


        $data = Acos::select(
            DB::raw("acos.id as aco_id,
            acos.nama as nama,
            acos.class as class,
            (case when isnull(acl.role_id,0)=0 then 
                    " . DB::raw($nonaktif) . " 
                    else 
                    " . DB::raw($aktif) . " 
                    end) as status
            ")
        )
            ->leftJoin('acl', function ($join)  use ($param1) {
                $join->on('acos.id', '=', 'acl.aco_id');
                $join->on('acl.role_id', '=', DB::raw("'" . $param1 . "'"));
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
