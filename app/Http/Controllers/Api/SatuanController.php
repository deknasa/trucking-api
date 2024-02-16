<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Satuan;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreSatuanRequest;
use App\Http\Requests\UpdateSatuanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;

class SatuanController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {

        DB::beginTransaction();

        try {
            $satuan = new Satuan();

            return response([
                'data' => $satuan->get(),
                'attributes' => [
                    'totalRows' => $satuan->totalRows,
                    'totalPages' => $satuan->totalPages
                ]
            ]);
         } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
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
     * @Keterangan TAMBAH DATA
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
        DB::beginTransaction();
        try {
            return response([
                'status' => true,
                'data' => (new Satuan())->findAll($satuan->id)
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
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
     * @Keterangan HAPUS DATA
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
            (new Satuan())->processApprovalnonaktif($data);

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
