<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHariLiburRequest;
use App\Http\Requests\UpdateHariLiburRequest;
use App\Http\Requests\DestroyHariLiburRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class HariLiburController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $hariLibur = new HariLibur();
        return response([
            'data' => $hariLibur->get(),
            'attributes' => [
                'totalRows' => $hariLibur->totalRows,
                'totalPages' => $hariLibur->totalPages
            ]
        ]);
    }

    public function default()
    {
        $hariLibur = new HariLibur();
        return response([
            'status' => true,
            'data' => $hariLibur->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreHariLiburRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tgl' => date('Y-m-d', strtotime($request->tgl)),
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
            ];
            $hariLibur = (new HariLibur())->processStore($data);
            $hariLibur->position = $this->getPosition($hariLibur, $hariLibur->getTable())->position;
            if ($request->limit==0) {
                $hariLibur->page = ceil($hariLibur->position / (10));
            } else {
                $hariLibur->page = ceil($hariLibur->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hariLibur
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $hariLibur = HariLibur::where('id', $id)->first();
        return response([
            'status' => true,
            'data' => $hariLibur
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateHariLiburRequest $request, HariLibur $harilibur)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tgl' => date('Y-m-d', strtotime($request->tgl)),
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
            ];

            $harilibur = (new harilibur())->processUpdate($harilibur, $data);
            $harilibur->position = $this->getPosition($harilibur, $harilibur->getTable())->position;
            if ($request->limit==0) {
                $harilibur->page = ceil($harilibur->position / (10));
            } else {
                $harilibur->page = ceil($harilibur->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $harilibur
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroyHariLiburRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $harilibur = (new HariLibur())->processDestroy($id);
            $selected = $this->getPosition($harilibur, $harilibur->getTable(), true);
            $harilibur->position = $selected->position;
            $harilibur->id = $selected->id;
            if ($request->limit==0) {
                $harilibur->page = ceil($harilibur->position / (10));
            } else {
                $harilibur->page = ceil($harilibur->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $harilibur
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('harilibur')->getColumns();

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

            header('Access-Control-Allow-Origin: *');

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $hariLiburs = $decodedResponse['data'];

            $judulLaporan = $hariLiburs[0]['judulLaporan'];

            $i = 0;
            foreach ($hariLiburs as $index => $params) {


                $statusaktif = $params['statusaktif'];


                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $hariLiburs[$i]['statusaktif'] = $statusaktif;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',

                ],
                [
                    'label' => 'Tanggal',
                    'index' => 'tgl',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Status',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $hariLiburs, $columns);
        }
    }
}
