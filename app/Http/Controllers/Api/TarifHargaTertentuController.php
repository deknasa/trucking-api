<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\TarifHargaTertentu;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreTarifHargaTertentuRequest;
use App\Http\Requests\UpdateTarifHargaTertentuRequest;

class TarifHargaTertentuController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $tarifHargaTertentu = new TarifHargaTertentu();
        return response([
            'data' => $tarifHargaTertentu->get(),
            'attributes' => [
                'totalRows' => $tarifHargaTertentu->totalRows,
                'totalPages' => $tarifHargaTertentu->totalPages
            ]
        ]);
    }

    public function default()
    {
        $tarifHargaTertentu = new TarifHargaTertentu();
        return response([
            'status' => true,
            'data' => $tarifHargaTertentu->default()
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTarifHargaTertentuRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tarif_id' => $request->tarif_id,
                'container_id' => $request->container_id,
                'tujuanbongkar' => $request->tujuanbongkar,
                'lokasidooring' => $request->lokasidooring,
                'lokasidooring_id' => $request->lokasidooring_id,
                'shipper' => $request->shipper,
                'nominal' => $request->nominal,
                'cabang' => $request->cabang,
                'statuscabang' => $request->statuscabang,
                'statusaktif' => $request->statusaktif,
            ];
            $tarifHargaTertentu = (new TarifHargaTertentu())->processStore($data);
            $tarifHargaTertentu->position = $this->getPosition($tarifHargaTertentu, $tarifHargaTertentu->getTable())->position;
            if ($request->limit == 0) {
                $tarifHargaTertentu->page = ceil($tarifHargaTertentu->position / (10));
            } else {
                $tarifHargaTertentu->page = ceil($tarifHargaTertentu->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $tarifHargaTertentu
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $tarifHargaTertentu = (new TarifHargaTertentu())->findAll($id);

        return response([
            'status' => true,
            'data' => $tarifHargaTertentu
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTarifHargaTertentuRequest $request, TarifHargaTertentu $tarifhargatertentu): JsonResponse
    {

        DB::beginTransaction();

        try {

            $data = [
                'tarif_id' => $request->tarif_id,
                'container_id' => $request->container_id,
                'tujuanbongkar' => $request->tujuanbongkar,
                'lokasidooring' => $request->lokasidooring,
                'lokasidooring_id' => $request->lokasidooring_id,
                'shipper' => $request->shipper,
                'nominal' => $request->nominal,
                'cabang' => $request->cabang,
                'statuscabang' => $request->statuscabang,
                'statusaktif' => $request->statusaktif,
            ];
            $tarifHargaTertentu = (new TarifHargaTertentu())->processUpdate($tarifhargatertentu, $data);
            $tarifHargaTertentu->position = $this->getPosition($tarifHargaTertentu, $tarifHargaTertentu->getTable())->position;
            if ($request->limit == 0) {
                $tarifHargaTertentu->page = ceil($tarifHargaTertentu->position / (10));
            } else {
                $tarifHargaTertentu->page = ceil($tarifHargaTertentu->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $tarifHargaTertentu
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
    public function destroy(Request $request, $id): JsonResponse
    {

        DB::beginTransaction();

        try {
            $tarifHargaTertentu = (new TarifHargaTertentu())->processDestroy($id);
            $selected = $this->getPosition($tarifHargaTertentu, $tarifHargaTertentu->getTable(), true);
            $tarifHargaTertentu->position = $selected->position;
            $tarifHargaTertentu->id = $selected->id;
            if ($request->limit == 0) {
                $tarifHargaTertentu->page = ceil($tarifHargaTertentu->position / (10));
            } else {
                $tarifHargaTertentu->page = ceil($tarifHargaTertentu->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $tarifHargaTertentu
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('tarifhargatertentu')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    public function cekValidasi($id)
    {
        $dataMaster = TarifHargaTertentu::where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        if ($useredit != '' && $useredit != $user) {
           
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('TarifHargaTertentu', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' .$dataMaster->email . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            
        } else {
            
            (new MyModel())->updateEditingBy('TarifHargaTertentu', $id, $aksi);
                
            $data = [
                'error' => false,
                'message' => '',
                'kodeerror' => '',
                'statuspesan' => 'success',
            ];
            

            return response($data);
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

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $tarifs = $decodedResponse['data'];
            $judulLaporan = $tarifs[0]['judulLaporan'];

            $i = 0;
            foreach ($tarifs as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statuscabang = $params['statuscabang'];

                $result = json_decode($statusaktif, true);
                $resultcabang = json_decode($statuscabang, true);

                $statusaktif = $result['MEMO'];
                $statuscabang = $resultcabang['MEMO'];


                $tarifs[$i]['statusaktif'] = $statusaktif;
                $tarifs[$i]['statuscabang'] = $statuscabang;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Tujuan',
                    'index' => 'tujuan',
                ],
                [
                    'label' => 'Penyesuaian',
                    'index' => 'penyesuaian',
                ],
                [
                    'label' => 'Lokasi Dooring',
                    'index' => 'lokasidooring',
                ],
                [
                    'label' => 'Container',
                    'index' => 'container',
                ],
                [
                    'label' => 'Shipper',
                    'index' => 'shipper',
                ],
                [
                    'label' => 'Nominal',
                    'index' => 'nominal',
                ],
                [
                    'label' => 'Status Cabang',
                    'index' => 'statuscabang',
                ],
                [
                    'label' => 'Status',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $tarifs, $columns);
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
            (new TarifHargaTertentu())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
