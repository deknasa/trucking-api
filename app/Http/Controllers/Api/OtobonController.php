<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreOtobonRequest;
use App\Http\Requests\UpdateOtobonRequest;
use App\Models\Otobon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtobonController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $otobon = new Otobon();
        return response([
            'data' => $otobon->get(),
            'attributes' => [
                'totalRows' => $otobon->totalRows,
                'totalPages' => $otobon->totalPages
            ]
        ]);
    }

    
    /**
     * @ClassName 
     */
    public function store(StoreOtobonRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'agen_id' => $request->agen_id,
                'container_id' => $request->container_id,
                'nominal' => $request->nominal,
            ];

            $otobon = (new Otobon())->processStore($data);
            $otobon->position = $this->getPosition($otobon, $otobon->getTable())->position;
            if ($request->limit==0) {
                $otobon->page = ceil($otobon->position / (10));
            } else {
                $otobon->page = ceil($otobon->position / ($request->limit ?? 10));
            }
            
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $otobon,
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
            'data' => (new Otobon())->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateOtobonRequest $request, Otobon $otobon): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'agen_id' => $request->agen_id,
                'container_id' => $request->container_id,
                'nominal' => $request->nominal,
            ];

            $otobon = (new Otobon())->processUpdate($otobon, $data);
            $otobon->position = $this->getPosition($otobon, $otobon->getTable())->position;
            if ($request->limit==0) {
                $otobon->page = ceil($otobon->position / (10));
            } else {
                $otobon->page = ceil($otobon->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $otobon
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
            $otobon = (new Otobon())->processDestroy($id);
            $selected = $this->getPosition($otobon, $otobon->getTable(), true);
            $otobon->position = $selected->position;
            $otobon->id = $selected->id;
            if ($request->limit==0) {
                $otobon->page = ceil($otobon->position / (10));
            } else {
                $otobon->page = ceil($otobon->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $otobon
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('otobon')->getColumns();

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