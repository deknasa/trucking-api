<?php

namespace App\Http\Controllers\Api;

use App\Models\SubKelompok;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\StoreSubKelompokRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\UpdateSubKelompokRequest;
use App\Http\Requests\DestroySubKelompokRequest;

class SubKelompokController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $subKelompok = new SubKelompok();

        return response([
            'data' => $subKelompok->get(),
            'attributes' => [
                'totalRows' => $subKelompok->totalRows,
                'totalPages' => $subKelompok->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $subKelompok = new SubKelompok();
        $cekdata = $subKelompok->cekvalidasihapus($id);
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
        $subKelompok = new SubKelompok();
        return response([
            'status' => true,
            'data' => $subKelompok->default()
        ]);
    }
    public function show($id)
    {
        return response([
            'status' => true,
            'data' => (new SubKelompok())->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreSubKelompokRequest $request): JsonResponse
    {

        DB::beginTransaction();

        try {
            $data = [
                'kodesubkelompok' => $request->kodesubkelompok,
                'keterangan' => $request->keterangan ?? '',
                'kelompok_id' => $request->kelompok_id,
                'statusaktif' => $request->statusaktif
            ];
            $subKelompok = (new SubKelompok())->processStore($data);
            $subKelompok->position = $this->getPosition($subKelompok, $subKelompok->getTable())->position;
            if ($request->limit==0) {
                $subKelompok->page = ceil($subKelompok->position / (10));
            } else {
                $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $subKelompok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateSubKelompokRequest $request, SubKelompok $subKelompok): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodesubkelompok' => $request->kodesubkelompok,
                'keterangan' => $request->keterangan ?? '',
                'kelompok_id' => $request->kelompok_id,
                'statusaktif' => $request->statusaktif
            ];

            $subKelompok = (new SubKelompok())->processUpdate($subKelompok, $data);
            $subKelompok->position = $this->getPosition($subKelompok, $subKelompok->getTable())->position;
            if ($request->limit==0) {
                $subKelompok->page = ceil($subKelompok->position / (10));
            } else {
                $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $subKelompok
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
    public function destroy(DestroySubKelompokRequest $request, $id)
    {
        try {
            $subKelompok = (new SubKelompok())->processDestroy($id);
            $selected = $this->getPosition($subKelompok, $subKelompok->getTable(), true);
            $subKelompok->position = $selected->position;
            $subKelompok->id = $selected->id;
            if ($request->limit==0) {
                $subKelompok->page = ceil($subKelompok->position / (10));
            } else {
                $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $subKelompok
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
            (new SubKelompok())->processApprovalnonaktif($data);

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
            $subKelompoks = $decodedResponse['data'];

            $judulLaporan = $subKelompoks[0]['judulLaporan'];

            $i = 0;
            foreach ($subKelompoks as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $subKelompoks[$i]['statusaktif'] = $statusaktif;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Sub Kelompok',
                    'index' => 'kodesubkelompok',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Kelompok',
                    'index' => 'kelompok_id',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $subKelompoks, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('subkelompok')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

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
}
