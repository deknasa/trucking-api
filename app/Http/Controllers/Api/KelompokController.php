<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Kelompok;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreKelompokRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateKelompokRequest;
use App\Http\Requests\DestroyKelompokRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;

class KelompokController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $kelompok = new Kelompok();

        return response([
            'data' => $kelompok->get(),
            'attributes' => [
                'totalRows' => $kelompok->totalRows,
                'totalPages' => $kelompok->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $kelompok = new Kelompok();
        $cekdata = $kelompok->cekvalidasihapus($id);
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
        $kelompok = new Kelompok();
        return response([
            'status' => true,
            'data' => $kelompok->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreKelompokRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodekelompok' => $request->kodekelompok,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif
            ];
            $kelompok = (new Kelompok())->processStore($data);
            $kelompok->position = $this->getPosition($kelompok, $kelompok->getTable())->position;
            if ($request->limit==0) {
                $kelompok->page = ceil($kelompok->position / (10));
            } else {
                $kelompok->page = ceil($kelompok->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kelompok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Kelompok $kelompok)
    {
        return response([
            'status' => true,
            'data' => (new Kelompok())->findAll($kelompok->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateKelompokRequest $request, Kelompok $kelompok): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodekelompok' => $request->kodekelompok,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif
            ];

            $kelompok = (new Kelompok())->processUpdate($kelompok, $data);
            $kelompok->position = $this->getPosition($kelompok, $kelompok->getTable())->position;
            if ($request->limit==0) {
                $kelompok->page = ceil($kelompok->position / (10));
            } else {
                $kelompok->page = ceil($kelompok->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $kelompok
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyKelompokRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $kelompok = (new Kelompok())->processDestroy($id);
            $selected = $this->getPosition($kelompok, $kelompok->getTable(), true);
            $kelompok->position = $selected->position;
            $kelompok->id = $selected->id;
            if ($request->limit==0) {
                $kelompok->page = ceil($kelompok->position / (10));
            } else {
                $kelompok->page = ceil($kelompok->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $kelompok
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
            (new Kelompok())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kelompok')->getColumns();

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

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $kelompoks = $decodedResponse['data'];

            $judulLaporan = $kelompoks[0]['judulLaporan'];

            $i = 0;
            foreach ($kelompoks as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $kelompoks[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kelompok',
                    'index' => 'kodekelompok',
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

            $this->toExcel($judulLaporan, $kelompoks, $columns);
        }
    }
}
