<?php

namespace App\Http\Controllers\Api;

use App\Models\Cabang;
use App\Models\AkunPusat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreAkunPusatRequest;
use App\Http\Requests\UpdateAkunPusatRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\DestroyAkunPusatRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\TransferAkunPusatRequest;
use App\Models\MyModel;
use App\Models\Error;
use DateTime;
use App\Models\Parameter;

class AkunPusatController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $akunPusat = new AkunPusat();

        return response([
            'data' => $akunPusat->get(),
            'attributes' => [
                'totalRows' => $akunPusat->totalRows,
                'totalPages' => $akunPusat->totalPages
            ]
        ]);
    }
    public function default()
    {
        $akunPusat = new AkunPusat();
        return response([
            'status' => true,
            'data' => $akunPusat->default()
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreAkunPusatRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'coa' => $request->coa,
                'keterangancoa' => $request->keterangancoa,
                'type' => $request->type,
                'type_id' => $request->type_id,
                'akuntansi_id' => $request->akuntansi_id,
                'parent' => $request->parent,
                'level' => $request->level,
                'statusparent' => $request->statusparent,
                'statusneraca' => $request->statusneraca,
                'statuslabarugi' => $request->statuslabarugi,
                'coamain' => $request->coamain,
                'statusaktif' => $request->statusaktif,
            ];
            $akunPusat = (new AkunPusat())->processStore($data);
            $akunPusat->position = $this->getPosition($akunPusat, $akunPusat->getTable())->position;
            if ($request->limit == 0) {
                $akunPusat->page = ceil($akunPusat->position / (10));
            } else {
                $akunPusat->page = ceil($akunPusat->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $akunPusat
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $akunPusat = (new AkunPusat())->findAll($id);
        return response([
            'status' => true,
            'data' => $akunPusat
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateAkunPusatRequest $request, AkunPusat $akunPusat): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'coa' => $request->coa,
                'keterangancoa' => $request->keterangancoa,
                'type' => $request->type,
                'type_id' => $request->type_id,
                'akuntansi_id' => $request->akuntansi_id,
                'parent' => $request->parent,
                'statusparent' => $request->statusparent,
                'statusneraca' => $request->statusneraca,
                'statuslabarugi' => $request->statuslabarugi,
                'coamain' => $request->coamain,
                'statusaktif' => $request->statusaktif,
            ];
            $akunPusat = (new AkunPusat())->processUpdate($akunPusat, $data);
            $akunPusat->position = $this->getPosition($akunPusat, $akunPusat->getTable())->position;
            if ($request->limit == 0) {
                $akunPusat->page = ceil($akunPusat->position / (10));
            } else {
                $akunPusat->page = ceil($akunPusat->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $akunPusat
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
    public function destroy(DestroyAkunPusatRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $akunPusat = (new AkunPusat())->processDestroy($id);
            $selected = $this->getPosition($akunPusat, $akunPusat->getTable(), true);
            $akunPusat->position = $selected->position;
            $akunPusat->id = $selected->id;
            if ($request->limit == 0) {
                $akunPusat->page = ceil($akunPusat->position / (10));
            } else {
                $akunPusat->page = ceil($akunPusat->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $akunPusat
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function deleteCoa(Request $request)
    {
        DB::beginTransaction();

        try {
            $akunPusat = (new AkunPusat())->processDeleteCoa($request->coa);
            $selected = $this->getPosition($akunPusat, $akunPusat->getTable(), true);
            $akunPusat->position = $selected->position;
            $akunPusat->id = $selected->id;
            $akunPusat->page = ceil($akunPusat->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $akunPusat
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
            (new AkunPusat())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function checkCoa(Request $request)
    {

        $akunPusat = (new AkunPusat())->checkTransferData($request->coa);
        if ($akunPusat == null) {
            return response()->json([
                'status' => true,
                'message' => 'tidak ada data yang sama'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'ada data yang sama'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('akunPusat')->getColumns();

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
            $akunpusats = $decodedResponse['data'];

            $judulLaporan = $akunpusats[0]['judulLaporan'];


            $i = 0;
            foreach ($akunpusats as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusParent = $params['statusparent'];
                $statusNeraca = $params['statusneraca'];
                $statusLabaRugi = $params['statuslabarugi'];

                $result = json_decode($statusaktif, true);
                $resultParent = json_decode($statusParent, true);
                $resultNeraca = json_decode($statusNeraca, true);
                $resultLabaRugi = json_decode($statusLabaRugi, true);

                $format = $result['MEMO'];
                $statusParent = ($resultParent != '') ? $resultParent['MEMO'] : '';
                $statusNeraca = $resultNeraca['MEMO'];
                $statusLabaRugi = $resultLabaRugi['MEMO'];


                $akunpusats[$i]['statusaktif'] = $format;
                $akunpusats[$i]['statusparent'] = $statusParent;
                $akunpusats[$i]['statusneraca'] = $statusNeraca;
                $akunpusats[$i]['statuslabarugi'] = $statusLabaRugi;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Perkiraan',
                    'index' => 'coa',
                ],
                [
                    'label' => 'Nama Perkiraan',
                    'index' => 'keterangancoa',
                ],
                [
                    'label' => 'Type',
                    'index' => 'type',
                ],
                [
                    'label' => 'Parent',
                    'index' => 'parent',
                ],
                [
                    'label' => 'Kode Perkiraan Pusat',
                    'index' => 'coamain',
                ],
                [
                    'label' => 'Status Parent',
                    'index' => 'statusparent',
                ],
                [
                    'label' => 'Status Neraca',
                    'index' => 'statusneraca',
                ],
                [
                    'label' => 'Status Laba Rugi',
                    'index' => 'statuslabarugi',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],

            ];
            $this->toExcel($judulLaporan, $akunpusats, $columns);
        }
    }

    public function cekValidasi($id)
    {
        $akunPusat = new AkunPusat();
        $cekdata = $akunPusat->cekvalidasihapus($id);
        $dataMaster = AkunPusat::where('id',$id)->first();
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

                    (new MyModel())->updateEditingBy('akunpusat', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->keterangancoa . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            

            } else if ($cekdata['kondisi'] == true) {
            // $query = DB::table('error')
            //     ->select(
            //         DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
            //     )
            //     ->where('kodeerror', '=', 'SATL')
            //     ->get();
            // $keterangan = $query['0'];
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $keterror = 'Data <b>' . $dataMaster->keterangancoa . '</b><br>' . $keteranganerror . ' <b> <br> ' . $keterangantambahanerror;

            $data = [
                // 'status' => false,
                // 'message' => $keterangan,
                // 'errors' => '',
                // 'kondisi' => $cekdata['kondisi'],
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SATL2',
                'statuspesan' => 'warning',

            ];

            return response($data);

        } else {
            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->updateEditingBy('akunpusat', $id, $aksi);
            }            
            $data = [
                'error' => false,
                'message' => '',
                'kodeerror' => '',
                'statuspesan' => 'success',                
                // 'status' => false,
                // 'message' => '',
                // 'errors' => '',
                // 'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }



    /**
     * @ClassName 
     * @Keterangan TRANSFER DATA KE CABANG LAIN
     */
    public function transfer(TransferAkunPusatRequest $request)
    {
        DB::beginTransaction();

        try {

            $cabangCheck = [];
            $dataCoa = [];
            $msg = [];

            $returnArray = [];

            for ($x = 0; $x < count($request->cabang); $x++) {
                $messages = []; // Array to store messages for each cabang
                $messages200 = []; // Array to store messages for statuscode 200
                $dataCoa = [];

                for ($i = 0; $i < count($request->coaId); $i++) {
                    $akunPusat = (new AkunPusat())->findAll($request->coaId[$i]);

                    $transferToCabang = $this->checkToCabang($request->cabang[$x], $akunPusat);

                    if ($transferToCabang['statuscode'] == 200) {
                        if ($transferToCabang['data']['status'] == false) {
                            $messages200[] = $transferToCabang['cabang'] . ' : sudah pernah input';
                            $dataCoa[] = $akunPusat->coa;
                        }
                    } else if ($transferToCabang['statuscode'] == 500) {
                        $messages[] = $transferToCabang['cabang'] . ' : server sedang tidak bisa diakses';
                    } else {
                        $messages[] = $transferToCabang['cabang'] . ' : proses cek akun pusat belum ada';
                    }
                }
                if (!empty($messages200)) {
                    $data = implode(', ', $dataCoa);
                    $msg = array_unique($messages200);
                    $messages[] = $msg[0] . ' ' . $data;
                }
                // Add the messages to the return array
                if (!empty($messages)) {
                    $messages = array_unique($messages);
                    $returnArray[] = implode(', ', $messages);
                }
            }

            if (!empty($returnArray)) {
                return response([
                    'statuspesan' => 'warning',
                    'message' => $returnArray
                ], 500);
            }

            $returnArray = [];
            for ($x = 0; $x < count($request->cabang); $x++) {
                for ($i = 0; $i < count($request->coaId); $i++) {

                    $akunPusat = (new AkunPusat())->findAll($request->coaId[$i]);

                    $transferToCabang = $this->transferToCabang($request->cabang[$x], $akunPusat);

                    $statusCode[] = $transferToCabang['statuscode'];
                }
                if ($transferToCabang['statuscode'] != 201) {
                    $cabangError[] = $transferToCabang['cabang'] . ' : server sedang tidak bisa diakses';
                    $statusCodeError[] = $transferToCabang['statuscode'];
                }
                if (!empty($cabangError)) {
                    $returnArray = array_unique($cabangError);
                }
            }

            $errorCode = [422, 500];
            $check = [];
            foreach ($errorCode as $value) {
                if (in_array($value, $statusCode)) {
                    $check[] = true;
                }
            }
            if (count($check) > 0) {
                for ($x = 0; $x < count($request->cabang); $x++) {
                    for ($i = 0; $i < count($request->coaId); $i++) {

                        $akunPusat = (new AkunPusat())->findAll($request->coaId[$i]);

                        $deleteToCabang = $this->deleteToCabang($request->cabang[$x], $akunPusat->coa);

                        $statusCode[] = $deleteToCabang['statuscode'];
                    }
                }
                return response([
                    'message' => $returnArray
                ], 500);
            } else {
                return response([
                    'status' => true
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getCabang($cabangId)
    {
        $getCabang = Cabang::find($cabangId);
        if ($getCabang->kodecabang == 'MDN') {
            return [
                "cabang" => $getCabang->namacabang,
                "server" => config('app.server_mdn'),
            ];
        } else if ($getCabang->kodecabang == 'SBY') {
            return [
                "cabang" => $getCabang->namacabang,
                "server" => config('app.server_sby'),
            ];
        } else if ($getCabang->kodecabang == 'MKS') {
            return [
                "cabang" => $getCabang->namacabang,
                "server" => config('app.server_mks'),
            ];
        } else if ($getCabang->kodecabang == 'JKT') {
            return [
                "cabang" => $getCabang->namacabang,
                "server" => config('app.server_jkt'),
            ];
        }
    }

    public function transferToCabang($cabangId, $data)
    {
        // cek status code, kalau ada aja salahsatu yg bukan 200, langsung jalankan delete
        $cabang = $this->getCabang($cabangId);

        $tes = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($cabang['server'] . 'trucking-api/public/api/token', [
                'user' => config('app.user_api'),
                'password' => config('app.pass_api'),
            ]);
        $access_token = json_decode($tes, TRUE)['access_token'];
        $data = json_decode(json_encode($data), true);

        $transferAkunPusat = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        ])->post($cabang['server'] . 'trucking-api/public/api/akunpusat', $data);
        $tesResp = $transferAkunPusat->toPsrResponse();

        $response = [
            'statuscode' => $tesResp->getStatusCode(),
            'data' => $transferAkunPusat->json(),
            'cabang' => $cabang['cabang']
        ];

        return $response;
    }

    public function deleteToCabang($cabangId, $data)
    {
        // cek status code, kalau ada aja salahsatu yg bukan 200, langsung jalankan delete
        $cabang = $this->getCabang($cabangId);

        $tes = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($cabang['server'] . 'trucking-api/public/api/token', [
                'user' => config('app.user_api'),
                'password' => config('app.pass_api'),
            ]);
        $access_token = json_decode($tes, TRUE)['access_token'];

        $data = [
            'coa' => $data
        ];

        $transferAkunPusat = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        ])->delete($cabang['server'] . 'trucking-api/public/api/akunpusat/deleteCoa', $data);
        $tesResp = $transferAkunPusat->toPsrResponse();

        $response = [
            'statuscode' => $tesResp->getStatusCode(),
            'data' => $transferAkunPusat->json(),
            'cabang' => $cabang['cabang']
        ];

        return $response;
    }

    public function checkToCabang($cabangId, $data)
    {
        // cek status code, kalau ada aja salahsatu yg bukan 200, langsung jalankan delete
        $cabang = $this->getCabang($cabangId);
        $tes = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($cabang['server'] . 'trucking-api/public/api/token', [
                'user' => config('app.user_api'),
                'password' => config('app.pass_api'),
            ]);
        $access_token = json_decode($tes, TRUE)['access_token'];
        $data = json_decode(json_encode($data), true);

        $checkAkunPusat = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        ])->get($cabang['server'] . 'trucking-api/public/api/akunpusat/checkCoa', $data);
        $tesResp = $checkAkunPusat->toPsrResponse();
        $response = [
            'statuscode' => $tesResp->getStatusCode(),
            'data' => $checkAkunPusat->json(),
            'cabang' => $cabang['cabang']
        ];

        return $response;
    }
}
