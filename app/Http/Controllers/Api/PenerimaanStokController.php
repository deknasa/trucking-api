<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;
use App\Models\PenerimaanStok;
use App\Http\Requests\StorePenerimaanStokRequest;
use App\Http\Requests\UpdatePenerimaanStokRequest;
use App\Http\Requests\DestroyPenerimaanStokRequest;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class PenerimaanStokController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $penerimaanStok = new PenerimaanStok();
        return response([
            'data' => $penerimaanStok->get(),
            'attributes' => [
                'totalRows' => $penerimaanStok->totalRows,
                'totalPages' => $penerimaanStok->totalPages
            ]
        ]);
    }
    public function cekValidasi($id)
    {
        $penerimaanStok = new PenerimaanStok();
        $cekdata = $penerimaanStok->cekvalidasihapus($id);
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
        $penerimaanStok = new PenerimaanStok();
        return response([
            'status' => true,
            'data' => $penerimaanStok->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePenerimaanStokRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodepenerimaan' => $request->kodepenerimaan,
                'keterangan' => $request->keterangan ?? '',
                'coa' => $request->coa ?? '',
                'format' => $request->format ?? '',
                'statushitungstok' => $request->statushitungstok
            ];
            $penerimaanStok = (new PenerimaanStok())->processStore($data);
            $penerimaanStok->position = $this->getPosition($penerimaanStok, $penerimaanStok->getTable())->position;
            $penerimaanStok->page = ceil($penerimaanStok->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanStok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(PenerimaanStok $penerimaanStok, $id)
    {
        $penerimaanStok = new PenerimaanStok();
        return response([
            'data' => $penerimaanStok->find($id),
            'attributes' => [
                'totalRows' => $penerimaanStok->totalRows,
                'totalPages' => $penerimaanStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePenerimaanStokRequest $request, PenerimaanStok $penerimaanStok, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodepenerimaan' => $request->kodepenerimaan,
                'keterangan' => $request->keterangan ?? '',
                'coa' => $request->coa ?? '',
                'format' => $request->format ?? '',
                'statushitungstok' => $request->statushitungstok
            ];

            $penerimaanStok = PenerimaanStok::findOrFail($id);
            $penerimaanStok = (new PenerimaanStok())->processUpdate($penerimaanStok, $data);
            $penerimaanStok->position = $this->getPosition($penerimaanStok, $penerimaanStok->getTable())->position;
            $penerimaanStok->page = ceil($penerimaanStok->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $penerimaanStok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaanstok')->getColumns();

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
    public function destroy(DestroyPenerimaanStokRequest $request, $id)
    {
        DB::beginTransaction();


        try {
            $penerimaanStok = (new PenerimaanStok())->processDestroy($id);
            $selected = $this->getPosition($penerimaanStok, $penerimaanStok->getTable(), true);
            $penerimaanStok->position = $selected->position;
            $penerimaanStok->id = $selected->id;
            $penerimaanStok->page = ceil($penerimaanStok->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanStok
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
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
            $penerimaans = $decodedResponse['data'];

            $judulLaporan = $penerimaans[0]['judulLaporan'];

            $i = 0;
            foreach ($penerimaans as $index => $params) {

                $format = $params['format'];
                $statusHitungStok = $params['statushitungstok'];

                $result = json_decode($format, true);
                $resultHitungStok = json_decode($statusHitungStok, true);

                $format = $result['MEMO'];
                $statusHitungStok = $resultHitungStok['MEMO'];


                $penerimaans[$i]['format'] = $format;
                $penerimaans[$i]['statushitungstok'] = $statusHitungStok;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Penerimaan',
                    'index' => 'kodepenerimaan',
                ],
                [
                    'label' => 'keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'coa',
                    'index' => 'coa',
                ],
                [
                    'label' => 'status format',
                    'index' => 'format',
                ],
                [
                    'label' => 'status hitung stok',
                    'index' => 'statushitungstok',
                ],

            ];
            $this->toExcel($judulLaporan, $penerimaans, $columns);
        }
    }
}
