<?php

namespace App\Http\Controllers\Api;

use App\Models\Satuan;
use App\Http\Requests\StoreSatuanRequest;
use App\Http\Requests\UpdateSatuanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class SatuanController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $satuan = new Satuan();

        return response([
            'data' => $satuan->get(),
            'attributes' => [
                'totalRows' => $satuan->totalRows,
                'totalPages' => $satuan->totalPages
            ]
        ]);
    }

    public function default()
    {
        $satuan = new Satuan();
        return response([
            'status' => true,
            'data' => $satuan->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreSatuanRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'satuan' => $request->satuan,
                'statusaktif' => $request->statusaktif
            ];

            $satuan = (new Satuan())->processStore($data);
            $satuan->position = $this->getPosition($satuan, $satuan->getTable())->position;
            if ($request->limit==0) {
                $satuan->page = ceil($satuan->position / (10));
            } else {
                $satuan->page = ceil($satuan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $satuan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Satuan $satuan)
    {
        return response([
            'status' => true,
            'data' => $satuan
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateSatuanRequest $request, Satuan $satuan)
    {
        DB::beginTransaction();

        try {
            $data = [
                'satuan' => $request->satuan,
                'statusaktif' => $request->statusaktif
            ];

            $satuan = (new Satuan())->processUpdate($satuan, $data);
            $satuan->position = $this->getPosition($satuan, $satuan->getTable())->position;
            if ($request->limit==0) {
                $satuan->page = ceil($satuan->position / (10));
            } else {
                $satuan->page = ceil($satuan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $satuan
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
            $satuan = (new Satuan())->processDestroy($id);
            $selected = $this->getPosition($satuan, $satuan->getTable(), true);
            $satuan->position = $selected->position;
            $satuan->id = $selected->id;
            if ($request->limit==0) {
                $satuan->page = ceil($satuan->position / (10));
            } else {
                $satuan->page = ceil($satuan->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $satuan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('satuan')->getColumns();

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
            $satuans = $decodedResponse['data'];

            $judulLaporan = $satuans[0]['judulLaporan'];


            $i = 0;
            foreach ($satuans as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $satuans[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Satuan',
                    'index' => 'satuan',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $satuans, $columns);
        }
    }
}
