<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreGandenganRequest;
use App\Http\Requests\UpdateGandenganRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\Gandengan;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class GandenganController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $gandengan = new gandengan();
        return response([
            'data' => $gandengan->get(),
            'attributes' => [
                'totalRows' => $gandengan->totalRows,
                'totalPages' => $gandengan->totalPages
            ]
        ]);
    }


    /**
     * @ClassName 
     */
    public function store(StoreGandenganRequest $request)
    {
        DB::beginTransaction();
        try {
            $gandengan = new Gandengan();
            $gandengan->kodegandengan = $request->kodegandengan;
            $gandengan->keterangan = $request->keterangan;
            $gandengan->statusaktif = $request->statusaktif;
            $gandengan->modifiedby = auth('api')->user()->name;

            if ($gandengan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($gandengan->getTable()),
                    'postingdari' => 'ENTRY GANDENGAN',
                    'idtrans' => $gandengan->id,
                    'nobuktitrans' => $gandengan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $gandengan->toArray(),
                    'modifiedby' => $gandengan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($gandengan, $gandengan->getTable());
            $gandengan->position = $selected->position;
            $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gandengan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Gandengan  $gandengan
     * @return \Illuminate\Http\Response
     */
    public function show(Gandengan $gandengan)
    {
        return response([
            'status' => true,
            'data' => $gandengan
        ]);
    }



    /**
     * @ClassName 
     */
    public function update(UpdateGandenganRequest $request, Gandengan $gandengan)
    {
        DB::beginTransaction();
        try {
            $gandengan->kodegandengan = $request->kodegandengan;
            $gandengan->keterangan = $request->keterangan;
            $gandengan->statusaktif = $request->statusaktif;
            $gandengan->modifiedby = auth('api')->user()->name;

            if ($gandengan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($gandengan->getTable()),
                    'postingdari' => 'EDIT GANDENGAN',
                    'idtrans' => $gandengan->id,
                    'nobuktitrans' => $gandengan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $gandengan->toArray(),
                    'modifiedby' => $gandengan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($gandengan, $gandengan->getTable());
            $gandengan->position = $selected->position;
            $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $gandengan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(Gandengan $gandengan, Request $request)
    {
        DB::beginTransaction();

        try {
            $delete = Gandengan::destroy($gandengan->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($gandengan->getTable()),
                    'postingdari' => 'DELETE GANDENGAN',
                    'idtrans' => $gandengan->id,
                    'nobuktitrans' => $gandengan->id,
                    'aksi' => 'DELETE',
                    'datajson' => $gandengan->toArray(),
                    'modifiedby' => $gandengan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();


                /* Set position and page */
                $selected = $this->getPosition($gandengan, $gandengan->getTable(), true);
                $gandengan->position = $selected->position;
                $gandengan->id = $selected->id;
                $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $gandengan
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $gandengans = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Gandengan',
                'index' => 'kodegandengan',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel('Gandengan', $gandengans, $columns);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gandengan')->getColumns();

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
