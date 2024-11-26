<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StorePenjualRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPenjualRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\UpdatePenjualRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\MyModel;
use App\Models\Penjual;
use Illuminate\Http\JsonResponse;

class PenjualController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        // dd(request());
        $penjual = new Penjual();

        return response([
            'data' => $penjual->get(),
            'attributes' => [
                'totalRows' => $penjual->totalRows,
                'totalPages' => $penjual->totalPages
            ]
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePenjualRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $data = [
                'namapenjual' => $request->namapenjual,
                'alamat' => $request->alamat,
                'nohp' => $request->nohp,
                'coa' => $request->coa,
                'statusaktif' => $request->statusaktif
            ];

            $penjual = new Penjual();
            $penjual->processStore($data, $penjual);

            // $penjual->position = $this->getPosition($penjual, $penjual->getTable())->position;
            $posisi = $this->getPosition($penjual, $penjual->getTable());
            $penjual->position = $posisi->position;

            if ($request->limit == 0) {
                $penjual->page = ceil($penjual->position / (10));
            } else {
                $penjual->page = ceil($penjual->position / ($request->limit ?? 10));
            }
            // dd($penjual);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penjual
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

   
    public function show($id)
    {
        $penjual = new Penjual();
        $data = $penjual->getById($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePenjualRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();
        // dd($request, $id);
        try {
            $data = [
                'namapenjual' => $request->namapenjual,
                'alamat' => $request->alamat,
                'nohp' => $request->nohp,
                'coa' => $request->coa,
                'statusaktif' => $request->statusaktif
            ];

            $penjual = new Penjual();
            $penjuals = $penjual->findOrFail($id);
            $penjual = $penjual->processUpdate($penjuals, $data);
            // dd($penjuals);
            // if ($request->from == '') {
            $penjual->position = $this->getPosition($penjual, $penjual->getTable())->position;
            if ($request->limit == 0) {
                $penjual->page = ceil($penjual->position / (10));
            } else {
                $penjual->page = ceil($penjual->position / ($request->limit ?? 10));
            }
            // }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data Berhasil di update!',
                'data' => $penjual
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
    public function destroy(DestroyPenjualRequest $request, $id)
    {
        DB::beginTransaction();
        // dd($id, $request);
        try {
            $penjual = new Penjual();
            $penjuals = $penjual->findOrFail($id);
            $penjual = $penjual->processDestroy($penjuals);

            $selected = $this->getPosition($penjual, $penjual->getTable(), true);
            $penjual->position = $selected->position;
            $penjual->id = $selected->id;
            // dd($request->limit);
            if ($request->limit == 0) {
                $penjual->page = ceil($penjual->position / (10));
            } else {
                $penjual->page = ceil($penjual->position / ($request->limit ?? 10));
            }

            // dd($penjual);
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penjual
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function select2Coa(Request $request)
    {
        $penjual = new Penjual();
        return $penjual->select2Coa($request);
    }

    public function select2StatusAktif(Request $request){
        $penjual = new Penjual();
        return $penjual->select2StatusAktif($request);
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
    public function export(RangeExportReportRequest $request){
        // dd($request);
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
            $penjuals = $decodedResponse['data'];

            $judulLaporan = $penjuals[0]['judulLaporan'];

            // dd($response, $decodedResponse, $penjuals, $judulLaporan);
            $i = 0;
            foreach ($penjuals as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];

                $penjuals[$i]['statusaktif'] = $statusaktif;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Nama Penjual',
                    'index' => 'namapenjual',
                ],
                [
                    'label' => 'Alamat',
                    'index' => 'alamat',
                ],
                [
                    'label' => 'No. HP',
                    'index' => 'nohp',
                ],
                [
                    'label' => 'Ket. Coa',
                    'index' => 'coa',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Modified By',
                    'index' => 'modifiedby'
                ]

            ];

            $this->toExcel($judulLaporan, $penjuals, $columns);
        }
    }
}
