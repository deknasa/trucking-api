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
            
            $container->save();

            $datajson = [
                'id' => $container->id,
                'kodecontainer' => strtoupper($request->kodecontainer),
                'keterangan' => strtoupper($request->keterangan),
                'statusaktif' => $request->statusaktif,
                'modifiedby' => auth('api')->user()->name,
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
    public function update(StoreContainerRequest $request, Container $container)
    {
        DB::beginTransaction();
        try {
            $container->update(array_map('strtoupper', $request->validated()));

            $datajson = [
                'id' => $container->id,
                'kodecontainer' => strtoupper($request->kodecontainer),
                'keterangan' => strtoupper($request->keterangan),
                'statusaktif' => $request->statusaktif,
                'modifiedby' => auth('api')->user()->name,
            ];


            $datajson = [
                'id' => $container->id,
                'kodecontainer' => strtoupper($request->kodecontainer),
                'keterangan' => strtoupper($request->keterangan),
                'statusaktif' => $request->statusaktif,
                'modifiedby' => auth('api')->user()->name,
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Container  $container
     * @return \Illuminate\Http\Response
     */
          /**
     * @ClassName 
     */
    public function destroy(Container $container, Request $request)
    {
        DB::beginTransaction();
        try {

            $delete =Container::destroy($container->id);
            $del = 1;
            if ($delete) {
        
            $datajson = [
                'id' => $container->id,
                'kodecontainer' => strtoupper($request->kodecontainer),
                'keterangan' => strtoupper($request->keterangan),
                'statusaktif' => $request->statusaktif,
                'modifiedby' => auth('api')->user()->name,
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
            $data = $this->getid($container->id, $request, $del);
            $container->position = $data->row ?? 0;
            $container->id = $data->id ?? 0;
            if (isset($request->limit)) {
                $container->page = ceil($container->position / $request->limit);
            }

   

            // dd($cabang);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $container
            ]);
        }
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
