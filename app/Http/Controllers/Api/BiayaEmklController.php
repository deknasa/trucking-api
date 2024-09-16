<?php

namespace App\Http\Controllers\Api;


use DateTime;
use Carbon\Carbon;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;

use App\Models\BiayaEmkl;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBiayaEmklRequest;
use App\Http\Requests\UpdateBiayaEmklRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\ApprovalKaryawanRequest;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;


class BiayaEmklController extends Controller
{
 /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $biayaemkl = new BiayaEmkl();

        return response([
            'data' => $biayaemkl->get(),
            'attributes' => [
                'totalRows' => $biayaemkl->totalRows,
                'totalPages' => $biayaemkl->totalPages
            ]
        ]);
    }
    public function cekValidasi($id)
    {
        $biayaEmkl = new BiayaEmkl();
        $dataMaster = $biayaEmkl->where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $cekdata = $biayaEmkl->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {
            $keterangan = $error->cekKeteranganError('SATL') ?? '';

            $data = [
                'status' => false,
                'message' => $keterangan . " (" . $cekdata['keterangan'] . ")",
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('biayaEmkl', $id, $aksi);
                }

                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => false,
                    'editblok' => false,
                ];

                return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->keterangan . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;

                $data = [
                    'status' => true,
                    'message' => $keterror,
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];

                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('biayaEmkl', $id, $aksi);
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
        $biayaemkl = new BiayaEmkl();
        return response([
            'status' => true,
            'data' => $biayaemkl->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreBiayaEmklRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodebiayaemkl' => $request->kodebiayaemkl ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'keterangan' => $request->keterangan ?? '',
                'tas_id' => $request->tas_id,

            ];
            $biayaemkl = new BiayaEmkl();
            $biayaemkl->processStore($data, $biayaemkl);            
            if ($request->from == '') {
                $biayaemkl->position = $this->getPosition($biayaemkl, $biayaemkl->getTable())->position;
                if ($request->limit == 0) {
                    $biayaemkl->page = ceil($biayaemkl->position / (10));
                } else {
                    $biayaemkl->page = ceil($biayaemkl->position / ($request->limit ?? 10));
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $biayaemkl
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(BiayaEmkl $biayaemkl)
    {
        return response([
            'status' => true,
            'data' => (new BiayaEmkl())->findAll($biayaemkl->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateBiayaEmklRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodebiayaemkl' => $request->kodebiayaemkl ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'keterangan' => $request->keterangan ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',

            ];

            $biayaemkl = new BiayaEmkl();
            $biayaemkls = $biayaemkl->findOrFail($id);
            $biayaemkl = $biayaemkl->processUpdate($biayaemkls, $data);
            if ($request->from == '') {
                $biayaemkl->position = $this->getPosition($biayaemkl, $biayaemkl->getTable())->position;
                if ($request->limit == 0) {
                    $biayaemkl->page = ceil($biayaemkl->position / (10));
                } else {
                    $biayaemkl->page = ceil($biayaemkl->position / ($request->limit ?? 10));
                }
            }

            

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $biayaemkl
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
            $biayaemkl = new BiayaEmkl();
            $biayaemkls = $biayaemkl->findOrFail($id);
            $biayaemkl = $biayaemkl->processDestroy($biayaemkls);
            if ($request->from == '') {
                $selected = $this->getPosition($biayaemkl, $biayaemkl->getTable(), true);
                $biayaemkl->position = $selected->position;
                $biayaemkl->id = $selected->id;
                if ($request->limit == 0) {
                    $biayaemkl->page = ceil($biayaemkl->position / (10));
                } else {
                    $biayaemkl->page = ceil($biayaemkl->position / ($request->limit ?? 10));
                }
            }
           
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $biayaemkl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('biayaemkl')->getColumns();

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
            $biayaemkls = $decodedResponse['data'];

            $judulLaporan = $biayaemkls[0]['judulLaporan'];

            $i = 0;
            foreach ($biayaemkls as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $biayaemkls[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Biaya Emkl',
                    'index' => 'kodebiayaemkl',
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

            $this->toExcel($judulLaporan, $biayaemkls, $columns);
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
            (new BiayaEmkl())->processApprovalnonaktif($data);

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
     * @Keterangan APRROVAL AKTIF
     */
    public function approvalaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new BiayaEmkl())->processApprovalaktif($data);

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
