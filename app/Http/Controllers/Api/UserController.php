<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\DestroyUserRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\User;
use App\Models\Parameter;
use App\Models\Cabang;
use App\Models\LogTrail;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;


class UserController extends Controller
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

        $totalRows = User::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = User::select(
                'user.id',
                'user.user',
                'user.name',
                'cabang.namacabang as cabang_id',
                'user.karyawan_id',
                'user.dashboard',
                'parameter.text as statusaktif',
                'user.modifiedby',
                'user.created_at',
                'user.updated_at'
            )
                ->leftJoin('parameter', 'user.statusaktif', '=', 'parameter.id')
                ->leftJoin('cabang', 'user.cabang_id', '=', 'cabang.id')
                ->orderBy('user.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = User::select(
                    'user.id',
                    'user.user',
                    'user.name',
                    'cabang.namacabang as cabang_id',
                    'user.karyawan_id',
                    'user.dashboard',
                    'parameter.text as statusaktif',
                    'user.modifiedby',
                    'user.created_at',
                    'user.updated_at'
                )
                    ->leftJoin('parameter', 'user.statusaktif', '=', 'parameter.id')
                    ->leftJoin('cabang', 'user.cabang_id', '=', 'cabang.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('user.id', $params['sortOrder']);
            } else {
                $query = User::select(
                    'user.id',
                    'user.user',
                    'user.name',
                    'cabang.namacabang as cabang_id',
                    'user.karyawan_id',
                    'user.dashboard',
                    'parameter.text as statusaktif',
                    'user.modifiedby',
                    'user.created_at',
                    'user.updated_at'
                )
                    ->leftJoin('parameter', 'user.statusaktif', '=', 'parameter.id')
                    ->leftJoin('cabang', 'user.cabang_id', '=', 'cabang.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('user.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'cabang_id') {
                            $query = $query->where('cabang.namacabang', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else if ($search['field'] == 'cabang_id') {
                            $query = $query->orWhere('cabang.namacabang', 'LIKE', "%$search[data]%");
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

        $cabangs = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $cabangs,
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
     * @param  \App\Http\Requests\StoreCabangRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {

        DB::beginTransaction();
        try {
            $user = new User();
            $user->user = strtoupper($request->user);
            $user->name = strtoupper($request->name);
            $user->password = Hash::make($request->password);
            $user->cabang_id = $request->cabang_id;
            $user->karyawan_id = $request->karyawan_id;
            $user->dashboard = strtoupper($request->dashboard);
            $user->statusaktif = $request->statusaktif;
            $user->modifiedby = $request->modifiedby;


            if ($user->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($user->getTable()),
                    'postingdari' => 'ENTRY USER',
                    'idtrans' => $user->id,
                    'nobuktitrans' => $user->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $user->makeVisible(['password', 'remember_token'])->toArray(),
                    'modifiedby' => $user->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($user->id, $request, $del);
            $user->position = $data->row;
            // dd($user->position );
            if (isset($request->limit)) {
                $user->page = ceil($user->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $user
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return response([
            'status' => true,
            'data' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCabangRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        //   dd($request->all());
        DB::beginTransaction();
        try {
            $user = new User();
            $user = User::find($request->id);
            $user->user = strtoupper($request->user);
            $user->name = strtoupper($request->name);
            $user->cabang_id = $request->cabang_id;
            $user->karyawan_id = $request->karyawan_id;
            $user->dashboard = strtoupper($request->dashboard);
            $user->statusaktif = $request->statusaktif;
            $user->modifiedby = $request->modifiedby;

            if ($user->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($user->getTable()),
                    'postingdari' => 'EDIT USER',
                    'idtrans' => $user->id,
                    'nobuktitrans' => $user->id,
                    'aksi' => 'EDIT',
                    'datajson' => $user->makeVisible(['password', 'remember_token'])->toArray(),
                    'modifiedby' => $user->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $user->position = $this->getid($user->id, $request, 0)->row;


            if (isset($request->limit)) {
                $user->page = ceil($user->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $user
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user, Request $request)
    {
        DB::beginTransaction();
        try {
            if ($user->delete()) {
                $logTrail = [
                    'namatabel' => strtoupper($user->getTable()),
                    'postingdari' => 'DELETE USER',
                    'idtrans' => $user->id,
                    'nobuktitrans' => $user->id,
                    'aksi' => 'DELETE',
                    'datajson' => $user->makeVisible(['password', 'remember_token'])->toArray(),
                    'modifiedby' => $user->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            $del = 1;
            $data = $this->getid($user->id, $request, $del);
            $user->position = $data->row;
            $user->id = $data->id;

            if (isset($request->limit)) {
                $user->page = ceil($user->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $user
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('user')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

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
            $table->string('user', 255)->default('');
            $table->string('name', 255)->default('');
            $table->string('password', 255)->default('');
            $table->string('cabang_id', 300)->default('');
            $table->bigInteger('karyawan_id')->length(11)->default('0');
            $table->string('dashboard', 255)->default('');
            $table->string('statusaktif', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($params['sortname'] == 'id') {
            $query = User::select(
                'user.id as id_',
                'user.user',
                'user.name',
                'cabang.namacabang as cabang_id',
                'user.karyawan_id',
                'user.dashboard',
                'parameter.text as statusaktif',
                'user.modifiedby',
                'user.created_at',
                'user.updated_at'

            )
                ->leftJoin('parameter', 'user.statusaktif', '=', 'parameter.id')
                ->leftJoin('cabang', 'user.cabang_id', '=', 'cabang.id')
                ->orderBy('user.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = User::select(
                    'user.id as id_',
                    'user.user',
                    'user.name',
                    'cabang.namacabang as cabang_id',
                    'user.karyawan_id',
                    'user.dashboard',
                    'parameter.text as statusaktif',
                    'user.modifiedby',
                    'user.created_at',
                    'user.updated_at'
                )
                    ->leftJoin('parameter', 'user.statusaktif', '=', 'parameter.id')
                    ->leftJoin('cabang', 'user.cabang_id', '=', 'cabang.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('user.id', $params['sortorder']);
            } else {
                $query = User::select(
                    'user.id as id_',
                    'user.user',
                    'user.name',
                    'cabang.namacabang as cabang_id',
                    'user.karyawan_id',
                    'user.dashboard',
                    'parameter.text as statusaktif',
                    'user.modifiedby',
                    'user.created_at',
                    'user.updated_at'
                )
                    ->leftJoin('parameter', 'user.statusaktif', '=', 'parameter.id')
                    ->leftJoin('cabang', 'user.cabang_id', '=', 'cabang.id')

                    ->orderBy($params['sortname'], $params['sortorder'])

                    ->orderBy('user.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'user', 'name', 'cabang_id', 'karyawan_id', 'dashboard', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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

    public function combocabang(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
        if ($params['status'] == 'entry') {
            $query = Cabang::select('cabang.id as id', 'cabang.namacabang as namacabang')
                ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                ->where('parameter.text', "=", 'AKTIF');
        } else {
            Schema::create($temp, function ($table) {
                $table->string('id', 10)->default('');
                $table->string('namacabang', 150)->default(0);
                $table->string('param', 50)->default(0);
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'namacabang' => 'ALL',
                    'param' => '',
                ]
            );


            $queryall = Cabang::select('cabang.id as id', 'cabang.namacabang as namacabang', 'cabang.namacabang as param')
                ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                ->where('parameter.text', "=", 'AKTIF');

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data->toArray(),
        ]);
    }



    public function getuserid(Request $request)
    {

        $params = [
            'user' => $request->user ?? '',
        ];

        $query = User::select('id')
            ->where('user', "=", $params['user']);

        $data = $query->first();

        return response([
            'data' => $data
        ]);
    }
}
