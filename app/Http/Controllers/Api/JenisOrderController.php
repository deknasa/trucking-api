<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisOrder;
use App\Http\Requests\StoreJenisOrderRequest;
use App\Http\Requests\UpdateJenisOrderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
class JenisOrderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $jenisorder = new JenisOrder();

        return response([
            'data' => $jenisorder->get(),
            'attributes' => [
                'totalRows' => $jenisorder->totalRows,
                'totalPages' => $jenisorder->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreJenisOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenisorder = new JenisOrder();
            $jenisorder->kodejenisorder = $request->kodejenisorder;
            $jenisorder->statusaktif = $request->statusaktif;
            $jenisorder->keterangan = $request->keterangan;
            $jenisorder->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            TOP:
            if ($jenisorder->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisorder->getTable()),
                    'postingdari' => 'ENTRY JENIS ORDER',
                    'idtrans' => $jenisorder->id,
                    'nobuktitrans' => $jenisorder->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jenisorder->toArray(),
                    'modifiedby' => $jenisorder->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            // $del = 0;
            // $data = $this->getid($jenisorder->id, $request, $del);
            // $jenisorder->position = $data->row;

            // if (isset($request->limit)) {
            //     $jenisorder->page = ceil($jenisorder->position / $request->limit);
            // }

            /* Set position and page */
            $selected = $this->getPosition($jenisorder, $jenisorder->getTable());
            $jenisorder->position = $selected->position;
            $jenisorder->page = ceil($jenisorder->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenisorder
            ], 201);
        } catch (QueryException $queryException) {
            if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                // Check if deadlock
                if ($queryException->errorInfo[1] === 1205) {
                    goto TOP;
                }
            }

            throw $queryException;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    public function show(JenisOrder $jenisorder)
    {
        return response([
            'status' => true,
            'data' => $jenisorder
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(JenisOrder $jenisOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJenisOrderRequest  $request
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    /**
     * @ClassName 
     */
    public function update(StoreJenisOrderRequest $request, JenisOrder $jenisorder)
    {
        try {
            $jenisorder->kodejenisorder = $request->kodejenisorder;
            $jenisorder->keterangan = $request->keterangan;
            $jenisorder->statusaktif = $request->statusaktif;
            $jenisorder->modifiedby = auth('api')->user()->name;

            if ($jenisorder->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisorder->getTable()),
                    'postingdari' => 'EDIT JENIS ORDER',
                    'idtrans' => $jenisorder->id,
                    'nobuktitrans' => $jenisorder->id,
                    'aksi' => 'EDIT',
                    'datajson' => $jenisorder->toArray(),
                    'modifiedby' => $jenisorder->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                // $jenisorder->position = $this->getid($jenisorder->id, $request, 0)->row;

                // if (isset($request->limit)) {
                //     $jenisorder->page = ceil($jenisorder->position / $request->limit);
                // }

                /* Set position and page */
                $selected = $this->getPosition($jenisorder, $jenisorder->getTable());
                $jenisorder->position = $selected->position;
                $jenisorder->page = ceil($jenisorder->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $jenisorder
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JenisOrder  $jenisOrder
     * @return \Illuminate\Http\Response
     */
    /**
     * @ClassName 
     */
    public function destroy(JenisOrder $jenisorder, Request $request)
    {
        DB::beginTransaction();
        try {

            $delete = JenisOrder::destroy($jenisorder->id);
            $del = 1;
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisorder->getTable()),
                    'postingdari' => 'DELETE JENIS ORDER',
                    'idtrans' => $jenisorder->id,
                    'nobuktitrans' => $jenisorder->id,
                    'aksi' => 'DELETE',
                    'datajson' => $jenisorder->toArray(),
                    'modifiedby' => $jenisorder->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
                $data = $this->getid($jenisorder->id, $request, $del);

                /* Set position and page */
                $selected = $this->getPosition($jenisorder, $jenisorder->getTable(), true);
                $jenisorder->position = $selected->position;
                $jenisorder->id = $selected->id;
                $jenisorder->page = ceil($jenisorder->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $jenisorder
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenisorder')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
        ];

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
            $table->string('kodejenisorder', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new JenisOrder)->getTable())->select(
                'jenisorder.id as id_',
                'jenisorder.kodejenisorder',
                'jenisorder.keterangan',
                'jenisorder.statusaktif',
                'jenisorder.modifiedby',
                'jenisorder.created_at',
                'jenisorder.updated_at'
            )
                ->orderBy('jenisorder.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodejenisorder' or $params['sortname'] == 'keterangan') {
            $query = DB::table((new JenisOrder)->getTable())->select(
                'jenisorder.id as id_',
                'jenisorder.kodejenisorder',
                'jenisorder.keterangan',
                'jenisorder.statusaktif',
                'jenisorder.modifiedby',
                'jenisorder.created_at',
                'jenisorder.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('jenisorder.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new JenisOrder)->getTable())->select(
                    'jenisorder.id as id_',
                    'jenisorder.kodejenisorder',
                    'jenisorder.keterangan',
                    'jenisorder.statusaktif',
                    'jenisorder.modifiedby',
                    'jenisorder.created_at',
                    'jenisorder.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenisorder.id', $params['sortorder']);
            } else {
                $query = DB::table((new JenisOrder)->getTable())->select(
                    'jenisorder.id as id_',
                    'jenisorder.kodejenisorder',
                    'jenisorder.keterangan',
                    'jenisorder.statusaktif',
                    'jenisorder.modifiedby',
                    'jenisorder.created_at',
                    'jenisorder.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenisorder.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodejenisorder', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
