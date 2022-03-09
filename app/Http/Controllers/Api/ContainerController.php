<?php

namespace App\Http\Controllers\Api;

use App\Models\Container;
use App\Http\Requests\StoreContainerRequest;
use App\Http\Requests\UpdateContainerRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

class ContainerController extends Controller
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
        
        $totalRows = Container::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = Container::select(
                'container.id',
                'container.keterangan',
                'parameter.text as statusaktif',
                'container.modifiedby',
                'container.created_at',
                'container.updated_at'
            )
                ->leftJoin('parameter', 'container.statusaktif', '=', 'parameter.id')
                ->orderBy('container.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'keterangan') {
            $query = Container::select(
                'container.id',
                'container.keterangan',
                'parameter.text as statusaktif',
                'container.modifiedby',
                'container.created_at',
                'container.updated_at'
            )
                ->leftJoin('parameter', 'container.statusaktif', '=', 'parameter.id')
                ->orderBy('container.keterangan', $params['sortOrder'])
                ->orderBy('container.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Container::select(
                    'container.id',
                    'container.keterangan',
                    'parameter.text as statusaktif',
                    'container.modifiedby',
                    'container.created_at',
                    'container.updated_at'
                )
                    ->leftJoin('parameter', 'container.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('container.id', $params['sortOrder']);
            } else {
                $query = Container::select(
                    'container.id',
                    'container.keterangan',
                    'parameter.text as statusaktif',
                    'container.modifiedby',
                    'container.created_at',
                    'container.updated_at'
                )
                    ->leftJoin('parameter', 'container.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('container.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
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

        $containers = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $containers,
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
     * @param  \App\Http\Requests\StoreContainerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreContainerRequest $request)
    {
        DB::beginTransaction();
        try {
            $container = new Container();
            $container->keterangan = strtoupper($request->keterangan);
            $container->statusaktif = $request->statusaktif;
            $container->modifiedby = strtoupper($request->modifiedby);

            $container->save();

            $datajson = [
                'id' => $container->id,
                'keterangan' => strtoupper($request->keterangan),
                'statusaktif' => $request->statusaktif,
                'modifiedby' => strtoupper($request->modifiedby),
            ];



            $datalogtrail = [
                'namatabel' => 'CONTAINER',
                'postingdari' => 'ENTRY CONTAINER',
                'idtrans' => $container->id,
                'nobuktitrans' => $container->id,
                'aksi' => 'ENTRY',
                'datajson' => json_encode($datajson),
                'modifiedby' => $container->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            /* Set position and page */
            $del = 0;
            $data = $this->getid($container->id, $request, $del);
            $container->position = $data->row;
            // dd($container->position );
            if (isset($request->limit)) {
                $container->page = ceil($container->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $container
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Container  $container
     * @return \Illuminate\Http\Response
     */
    public function show(Container $container)
    {
        return response([
            'status' => true,
            'data' => $container
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Container  $container
     * @return \Illuminate\Http\Response
     */
    public function edit(Container $container)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateContainerRequest  $request
     * @param  \App\Models\Container  $container
     * @return \Illuminate\Http\Response
     */
    public function update(StoreContainerRequest $request, Container $container)
    {
        DB::beginTransaction();
        try {
            $container->update(array_map('strtoupper', $request->validated()));

            $datajson = [
                'id' => $container->id,
                'keterangan' => strtoupper($request->keterangan),
                'statusaktif' => $request->statusaktif,
                'modifiedby' => strtoupper($request->modifiedby),
            ];


            $datajson = [
                'id' => $container->id,
                'keterangan' => strtoupper($request->keterangan),
                'statusaktif' => $request->statusaktif,
                'modifiedby' => strtoupper($request->modifiedby),
            ];



            $datalogtrail = [
                'namatabel' => 'CONTAINER',
                'postingdari' => 'EDIT CONTAINER',
                'idtrans' => $container->id,
                'nobuktitrans' => $container->id,
                'aksi' => 'EDIT',
                'datajson' => json_encode($datajson),
                'modifiedby' => $container->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            DB::commit();

            /* Set position and page */


            $container->position = $this->getid($container->id, $request, 0)->row;

            if (isset($request->limit)) {
                $container->page = ceil($container->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $container
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Container  $container
     * @return \Illuminate\Http\Response
     */
    public function destroy(Container $container, Request $request)
    {
        DB::beginTransaction();
        try {

            Container::destroy($container->id);

            $datajson = [
                'id' => $container->id,
                'keterangan' => strtoupper($request->keterangan),
                'statusaktif' => $request->statusaktif,
                'modifiedby' => strtoupper($request->modifiedby),
            ];

            $datalogtrail = [
                'namatabel' => 'CONTAINER',
                'postingdari' => 'DELETE CONTAINER',
                'idtrans' => $container->id,
                'nobuktitrans' => $container->id,
                'aksi' => 'DELETE',
                'datajson' => json_encode($datajson),
                'modifiedby' => $container->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            Container::destroy($container->id);
            $del = 1;
            $data = $this->getid($container->id, $request, $del);
            $container->position = $data->row;
            $container->id = $data->id;
            if (isset($request->limit)) {
                $container->page = ceil($container->position / $request->limit);
            }
            // dd($cabang);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $container
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('container')->getColumns();

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
            $table->string('keterangan', 300)->default('');
            $table->string('statusaktif', 100)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($params['sortname'] == 'id') {
            $query = Container::select(
                'container.id as id_',
                'container.keterangan',
                'parameter.text as statusaktif',
                'container.modifiedby',
                'container.created_at',
                'container.updated_at'
            )
                ->leftJoin('parameter', 'container.statusaktif', '=', 'parameter.id')
                ->orderBy('container.id', $params['sortorder']);
        } else if ($params['sortname'] == 'keterangan') {
            $query = Container::select(
                'container.id as id_',
                'container.keterangan',
                'parameter.text as statusaktif',
                'container.modifiedby',
                'container.created_at',
                'container.updated_at'
            )
                ->leftJoin('parameter', 'container.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('container.keterangan', $params['sortorder'])
                ->orderBy('container.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Container::select(
                    'container.id as id_',
                    'container.keterangan',
                    'parameter.text as statusaktif',
                    'container.modifiedby',
                    'container.created_at',
                    'container.updated_at'
                )
                    ->leftJoin('parameter', 'container.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('container.id', $params['sortorder']);
            } else {
                $query = Container::select(
                    'container.id as id_',
                    'container.keterangan',
                    'parameter.text as statusaktif',
                    'container.modifiedby',
                    'container.created_at',
                    'container.updated_at'
                )
                    ->leftJoin('parameter', 'container.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])

                    ->orderBy('cabang.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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

}
