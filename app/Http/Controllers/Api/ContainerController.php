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
use Illuminate\Database\QueryException;

class ContainerController extends Controller
{


    /**
     * @ClassName 
     */
    public function index()
    {
        $container = new Container();

        return response([
            'data' => $container->get(),
            'attributes' => [
                'totalRows' => $container->totalRows,
                'totalPages' => $container->totalPages
            ]
        ]);
    }


    /**
     * @ClassName 
     */
    public function store(StoreContainerRequest $request)
    {
        DB::beginTransaction();
        try {
            $container = new Container();
            $container->kodecontainer = strtoupper($request->kodecontainer);
            $container->keterangan = strtoupper($request->keterangan);
            $container->statusaktif = $request->statusaktif;
            $container->modifiedby = auth('api')->user()->name;

            if ($container->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($container->getTable()),
                    'postingdari' => 'ENTRY CONTAINER',
                    'idtrans' => $container->id,
                    'nobuktitrans' => $container->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $container->toArray(),
                    'modifiedby' => $container->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($container, $container->getTable());
            $container->position = $selected->position;
            $container->page = ceil($container->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $container
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show(Container $container)
    {
        return response([
            'status' => true,
            'data' => $container
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateContainerRequest $request, Container $container)
    {
        DB::beginTransaction();
        try {
            $container->kodecontainer = $request->kodecontainer;
            $container->keterangan = $request->keterangan;
            $container->statusaktif = $request->statusaktif;
            $container->modifiedby = auth('api')->user()->name;

            if ($container->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($container->getTable()),
                    'postingdari' => 'EDIT CONTAINER',
                    'idtrans' => $container->id,
                    'nobuktitrans' => $container->id,
                    'aksi' => 'EDIT',
                    'datajson' => $container->toArray(),
                    'modifiedby' => $container->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($container, $container->getTable());
            $container->position = $selected->position;
            $container->page = ceil($container->position / ($request->limit ?? 10));

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
     * @ClassName 
     */
    public function destroy(Container $container, Request $request)
    {
        DB::beginTransaction();
        try {

            $delete = Container::destroy($container->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($container->getTable()),
                    'postingdari' => 'DELETE CONTAINER',
                    'idtrans' => $container->id,
                    'nobuktitrans' => $container->id,
                    'aksi' => 'DELETE',
                    'datajson' => $container->toArray(),
                    'modifiedby' => $container->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            $selected = $this->getPosition($container, $container->getTable(), true);
            $container->position = $selected->position;
            $container->id = $selected->id;
            $container->page = ceil($container->position / ($request->limit ?? 10));

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
}
