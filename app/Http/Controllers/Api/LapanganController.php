<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreLapanganRequest;
use App\Http\Requests\UpdateLapanganRequest;
use App\Models\Lapangan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LapanganController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $lapangan = new Lapangan();
        return response([
            'data' => $lapangan->get(),
            'attributes' => [
                'totalRows' => $lapangan->totalRows,
                'totalPages' => $lapangan->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreLapanganRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'agen_id' => $request->agen_id,
                'container_id' => $request->container_id,
                'nominal' => $request->nominal,
            ];

            $lapangan = (new Lapangan())->processStore($data);
            $lapangan->position = $this->getPosition($lapangan, $lapangan->getTable())->position;
            if ($request->limit==0) {
                $lapangan->page = ceil($lapangan->position / (10));
            } else {
                $lapangan->page = ceil($lapangan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $lapangan,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        return response([
            'status' => true,
            'data' => (new Lapangan())->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateLapanganRequest $request, Lapangan $lapangan): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'agen_id' => $request->agen_id,
                'container_id' => $request->container_id,
                'nominal' => $request->nominal,
            ];


            $lapangan = (new Lapangan())->processUpdate($lapangan, $data);
            $lapangan->position = $this->getPosition($lapangan, $lapangan->getTable())->position;
            if ($request->limit==0) {
                $lapangan->page = ceil($lapangan->position / (10));
            } else {
                $lapangan->page = ceil($lapangan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $lapangan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $lapangan = (new Lapangan())->processDestroy($id);
            $selected = $this->getPosition($lapangan, $lapangan->getTable(), true);
            $lapangan->position = $selected->position;
            $lapangan->id = $selected->id;
            if ($request->limit==0) {
                $lapangan->page = ceil($lapangan->position / (10));
            } else {
                $lapangan->page = ceil($lapangan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $lapangan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('lapangan')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
   
    /**
     * @ClassName 
     */
    public function report()
    {
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
            $otobon = $decodedResponse['data'];

            $judulLaporan = $otobon[0]['judulLaporan'];

            $i = 0;
          
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Customer',
                    'index' => 'agen',
                ],
                [
                    'label' => 'Container',
                    'index' => 'container',
                ],
                [
                    'label' => 'Nominal',
                    'index' => 'nominal',
                ],
            ];

            $this->toExcel($judulLaporan, $otobon, $columns);
        }
    }
}