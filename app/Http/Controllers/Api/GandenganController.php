<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreGandenganRequest;
use App\Http\Requests\UpdateGandenganRequest;
use App\Http\Requests\DestroyGandenganRequest;

use App\Http\Requests\StoreLogTrailRequest;

use App\Models\Gandengan;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\StokPersediaan;


use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

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
    public function report()
    {
    }
    public function cekValidasi($id)
    {
        $gandengan = new Gandengan();
        $cekdata = $gandengan->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }
    public function default()
    {

        $gandengan = new Gandengan();
        return response([
            'status' => true,
            'data' => $gandengan->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreGandenganRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodegandengan' => $request->kodegandengan,
                'keterangan' => $request->keterangan ?? '',
                'jumlahroda' => $request->jumlahroda,
                'jumlahbanserap' => $request->jumlahbanserap,
                'statusaktif' => $request->statusaktif,
            ];
            $gandengan = (new Gandengan())->processStore($data);
            $selected = $this->getPosition($gandengan, $gandengan->getTable());
            $gandengan->position = $selected->position;
            $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
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
    public function update(UpdateGandenganRequest $request, Gandengan $gandengan): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodegandengan' => $request->kodegandengan,
                'keterangan' => $request->keterangan ?? '',
                'jumlahroda' => $request->jumlahroda,
                'jumlahbanserap' => $request->jumlahbanserap,
                'statusaktif' => $request->statusaktif,
            ];
            $gandengan = (new Gandengan())->processUpdate($gandengan, $data);
            $gandengan->position = $this->getPosition($gandengan, $gandengan->getTable())->position;
            $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
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
    public function destroy(DestroyGandenganRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $gandengan = (new Gandengan())->processDestroy($id);
            $selected = $this->getPosition($gandengan, $gandengan->getTable(), true);
            $gandengan->position = $selected->position;
            $gandengan->id = $selected->id;
            $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
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
    public function export(RangeExportReportRequest $request)
    {
        if (request()->cekExport) {

            if (request()->offset == "-1" && request()->limit == '1') {
                
                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],
                    'status' => false,
                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'status' => true,
                ]);
            }
        } else {

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $gandengans = $decodedResponse['data'];

            $judulLaporan = $gandengans[0]['judulLaporan'];

            $i = 0;
            foreach ($gandengans as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $gandengans[$i]['statusaktif'] = $statusaktif;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
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

            $this->toExcel($judulLaporan, $gandengans, $columns);
        }
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
