<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StoreAclRequest;
use App\Http\Requests\UpdateAclRequest;
use App\Http\Requests\DestroyAclRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\Acl;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\Role;
use App\Models\Acos;

use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class AclController extends Controller
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
        $acl = new Acl();

        return response([
            'data' => $acl->get(),
            'attributes' => [
                'totalRows' => $acl->totalRows,
                'totalPages' => $acl->totalPages
            ]
        ]);
    }

    public function detail($roleId)
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

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
                ->where('acl.role_id', '=', $roleId)
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
                    ->where('acl.role_id', '=', $roleId)
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
                    ->where('acl.role_id', '=', $roleId)
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('acl.id', 'asc');
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
                        } else if ($filters['field'] == 'rolename') {
                            $query = $query->where('role.rolename', 'LIKE', "%$filters[data]%");
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
                        } else if ($filters['field'] == 'rolename') {
                            $query = $query->orWhere('role.rolename', 'LIKE', "%$filters[data]%");
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

        $acl = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

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
        /**
     * @ClassName 
     */
    public function store(StoreAclRequest $request)
    {
        DB::beginTransaction();
        try {

            

            for ($i = 0; $i < count($request->aco_id); $i++) {

                if ($request->status[$i] == 1) {
                    $acl = new Acl();
                    $acl->role_id = $request->role_id;
                    $acl->modifiedby = auth('api')->user()->name;
                    $acl->aco_id = $request->aco_id[$i]  ?? 0;
                    $acl->save();

                    $logTrail = [
                        'namatabel' => strtoupper($acl->getTable()),
                        'postingdari' => 'ENTRY ACL',
                        'idtrans' => $acl->id,
                        'nobuktitrans' => $acl->id,
                        'aksi' => 'ENTRY',
                        'datajson' => $acl->toArray(),
                        'modifiedby' => $acl->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    DB::commit();
                                                             
                }
            }

            $selected = $this->getPosition($acl, $acl->getTable());
            $acl->position = $selected->position;
            $acl->page = ceil($acl->position / ($request->limit ?? 10));
            // Log::info('selected', [
            //     'position' => $acl->position
            // ]);

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
    public function show($id)
    {
        $data = Role::select('id as role_id','rolename')
            ->where('id', '=',  $id)
            ->first();
        // $acl['rolename'] = $data['rolename'];

        // dd($acl);
        return response([
            'status' => true,
            'data' => $data
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
        /**
     * @ClassName 
     */
    public function update(UpdateAclRequest $request, $id)
    {
        // dd('test');
        DB::beginTransaction();
        try {
            Acl::where('role_id', $id)->delete();
            for ($i = 0; $i < count($request->aco_id); $i++) {
                if ($request->status[$i] == 1) {
                    $acl = new Acl();
                    
                    $acl->role_id = $request->role_id;
                    $acl->modifiedby = auth('api')->user()->name;
                    $acl->aco_id = $request->aco_id[$i]  ?? 0;

                    $acl->save();
                    $logTrail = [
                        'namatabel' => strtoupper($acl->getTable()),
                        'postingdari' => 'EDIT ACL',
                        'idtrans' => $acl->id,
                        'nobuktitrans' => $acl->id,
                        'aksi' => 'EDIT',
                        'datajson' => $acl->toArray(),
                        'modifiedby' => $acl->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                }
            }

            DB::commit();
            /* Set position and page */

            $selected = $this->getPosition($acl, $acl->getTable());
            $acl->position = $selected->position;
            $acl->page = ceil($acl->position / ($request->limit ?? 10));

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
        /**
     * @ClassName 
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();

        try {            
            $acl = new Acl();
            $get = Acl::select('id')->where('role_id',$id)->get();

            for($i = 0; $i < count($get); $i++) {
               $aclId = $get[$i]->id;
               $delete = Acl::destroy($aclId);
               $logTrail = [
                    'namatabel' => strtoupper($acl->getTable()),
                    'postingdari' => 'DELETE ACL',
                    'idtrans' => $aclId,
                    'nobuktitrans' => $aclId,
                    'aksi' => 'DELETE',
                    'datajson' => $acl->toArray(),
                    'modifiedby' => $acl->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }
            
            DB::commit();
            $selected = $this->getPosition($acl, $acl->getTable(), true);
            $acl->position = $selected->position;
            $acl->id = $selected->id;
            $acl->page = ceil($acl->position / ($request->limit ?? 10));

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

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $acls = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Role ID',
                'index' => 'role_id',
            ],
            [
                'label' => 'Role Name',
                'index' => 'rolename',
            ],
        ];

        $this->toExcel('Acl', $acls, $columns);
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

        $params = [
            'indexRow' => $request->indexRow ?? 1,
            'limit' => $request->limit ?? 100,
            'page' => $request->page ?? 1,
            'sortname' => $request->sortname ?? 'id',
            'sortorder' => $request->sortorder ?? 'asc',
        ];

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('role_id')->nullable();
            $table->bigInteger('id_')->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

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
        if ($params['sortname'] == 'rolename') {
            $query = DB::table($temp)
                ->select(
                    $temp . '.role_id as role_id',
                    $temp . '.id_ as id',
                    'role.rolename as rolename',
                    $temp . '.modifiedby as modifiedby',
                    $temp . '.updated_at as updated_at'
                )
                ->Join('role', 'role.id', '=', $temp . '.role_id')
                ->orderBy('role.rolename',  $params['sortorder']);
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
                ->orderBy($temp . '.' . $params['sortname'],  $params['sortorder']);
        }
        // 
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('role_id')->nullable();
            $table->bigInteger('id_')->nullable();
            $table->string('rolename', 300)->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('role_id');
        });

        DB::table($temp)->insertUsing(['role_id', 'id_',  'rolename',  'modifiedby', 'updated_at'], $query);


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
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
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
