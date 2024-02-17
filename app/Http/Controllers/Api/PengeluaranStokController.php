<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\PengeluaranStok;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StorePengeluaranStokRequest;
use App\Http\Requests\UpdatePengeluaranStokRequest;
use App\Http\Requests\DestroyPengeluaranStokRequest;

class PengeluaranStokController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $pengeluaranStok = new PengeluaranStok();
        return response([
            'data' => $pengeluaranStok->get(),
            'acos' => $pengeluaranStok->acos(),
            'attributes' => [
                'totalRows' => $pengeluaranStok->totalRows,
                'totalPages' => $pengeluaranStok->totalPages
            ]
        ]);
    }


    public function cekValidasi($id)
    {
        $pengeluaranStok = new PengeluaranStok();
        $cekdata = $pengeluaranStok->cekvalidasihapus($id);
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
        $pengeluaranStok = new PengeluaranStok();
        return response([
            'status' => true,
            'data' => $pengeluaranStok->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengeluaranStokRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodepengeluaran' => $request->kodepengeluaran,
                'keterangan' => $request->keterangan ?? '',
                'coa' => $request->coa ?? '',
                'format' => $request->format ?? '',
                'statushitungstok' => $request->statushitungstok ?? '',
                'statusaktif' => $request->statusaktif ?? 1,
            ];
            $pengeluaranStok = (new PengeluaranStok())->processStore($data);
            $pengeluaranStok->position = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable())->position;
            if ($request->limit==0) {
                $pengeluaranStok->page = ceil($pengeluaranStok->position / (10));
            } else {
                $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show(PengeluaranStok $pengeluaranStok, $id)
    {
        $pengeluaranStok = new PengeluaranStok();
        return response([
            'data' => $pengeluaranStok->find($id),
            'attributes' => [
                'totalRows' => $pengeluaranStok->totalRows,
                'totalPages' => $pengeluaranStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePengeluaranStokRequest $request, PengeluaranStok $pengeluaranStok, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodepengeluaran' => $request->kodepengeluaran,
                'keterangan' => $request->keterangan ?? '',
                'coa' => $request->coa ?? '',
                'format' => $request->format ?? '',
                'statushitungstok' => $request->statushitungstok ?? '',
                'statusaktif' => $request->statusaktif ?? 1,
            ];

            $pengeluaranStok = PengeluaranStok::findOrFail($id);
            $pengeluaranStok = (new PengeluaranStok())->processUpdate($pengeluaranStok, $data);
            $pengeluaranStok->position = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable())->position;
            if ($request->limit==0) {
                $pengeluaranStok->page = ceil($pengeluaranStok->position / (10));
            } else {
                $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $pengeluaranStok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluaranstok')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyPengeluaranStokRequest $request, $id)
    {
        DB::beginTransaction();


        try {
            $pengeluaranStok = (new PengeluaranStok())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable(), true);
            $pengeluaranStok->position = $selected->position;
            $pengeluaranStok->id = $selected->id;
            if ($request->limit==0) {
                $pengeluaranStok->page = ceil($pengeluaranStok->position / (10));
            } else {
                $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranStok
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

     /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new PengeluaranStok())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
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
            $pengeluarans = $decodedResponse['data'];

            $judulLaporan = $pengeluarans[0]['judulLaporan'];

            $i = 0;
            foreach ($pengeluarans as $index => $params) {

                $format = $params['format'];
                $statusHitungStok = $params['statushitungstok'];

                $result = json_decode($format, true);
                $resultHitungStok = json_decode($statusHitungStok, true);

                $format = $result['SINGKATAN'];
                $statusHitungStok = $resultHitungStok['MEMO'];


                $pengeluarans[$i]['format'] = $format;
                $pengeluarans[$i]['statushitungstok'] = $statusHitungStok;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Pengeluaran',
                    'index' => 'kodepengeluaran',
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
            $this->toExcel($judulLaporan, $pengeluarans, $columns);
        }
    }
}
