<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkunPusat;
use App\Http\Requests\StoreAkunPusatRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAkunPusatRequest;
use App\Http\Requests\DestroyAkunPusatRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\TransferAkunPusatRequest;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class AkunPusatController extends Controller
{
    /**
     * @ClassName 
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
                'statuscoa' => $request->statuscoa,
                'level' => $request->level,
                'statusaccountpayable' => $request->statusaccountpayable,
                'statusneraca' => $request->statusneraca,
                'statuslabarugi' => $request->statuslabarugi,
                'coamain' => $request->coamain,
                'statusaktif' => $request->statusaktif,
            ];
            $akunPusat = (new AkunPusat())->processStore($data);
            $akunPusat->position = $this->getPosition($akunPusat, $akunPusat->getTable())->position;
            $akunPusat->page = ceil($akunPusat->position / ($request->limit ?? 10));

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
                'statuscoa' => $request->statuscoa,
                'statusaccountpayable' => $request->statusaccountpayable,
                'statusneraca' => $request->statusneraca,
                'statuslabarugi' => $request->statuslabarugi,
                'coamain' => $request->coamain,
                'statusaktif' => $request->statusaktif,
            ];
            $akunPusat = (new AkunPusat())->processUpdate($akunPusat, $data);
            $akunPusat->position = $this->getPosition($akunPusat, $akunPusat->getTable())->position;
            $akunPusat->page = ceil($akunPusat->position / ($request->limit ?? 10));

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
     */
    public function destroy(DestroyAkunPusatRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $akunPusat = (new AkunPusat())->processDestroy($id);
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

    public function checkCoa(Request $request)
    {

        $akunPusat = (new AkunPusat())->checkTransferData($request->coa);
        if ($akunPusat == null) {
            return response()->json([
                'status' => true,
                'message' => 'tidak ada data yang sama'
            ]);
        }else{
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
            $akunpusats = $decodedResponse['data'];

            $judulLaporan = $akunpusats[0]['judulLaporan'];


            $i = 0;
            foreach ($akunpusats as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statuscoa = $params['statuscoa'];
                $statusAkunPayable = $params['statusaccountpayable'];
                $statusNeraca = $params['statusneraca'];
                $statusLabaRugi = $params['statuslabarugi'];

                $result = json_decode($statusaktif, true);
                $resultStatuscoa = json_decode($statuscoa, true);
                $resultAkunPayable = json_decode($statusAkunPayable, true);
                $resultNeraca = json_decode($statusNeraca, true);
                $resultLabaRugi = json_decode($statusLabaRugi, true);

                $format = $result['MEMO'];
                $statusStatuscoa = $resultStatuscoa['MEMO'];
                $statusAkunPayable = $resultAkunPayable['MEMO'];
                $statusNeraca = $resultNeraca['MEMO'];
                $statusLabaRugi = $resultLabaRugi['MEMO'];


                $akunpusats[$i]['statusaktif'] = $format;
                $akunpusats[$i]['statuscoa'] = $statusStatuscoa;
                $akunpusats[$i]['statusaccountpayable'] = $statusAkunPayable;
                $akunpusats[$i]['statusneraca'] = $statusNeraca;
                $akunpusats[$i]['statuslabarugi'] = $statusLabaRugi;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'COA',
                    'index' => 'coa',
                ],
                [
                    'label' => 'Keterangan COA',
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
                    'label' => 'COA Main',
                    'index' => 'coamain',
                ],
                [
                    'label' => 'Status COA',
                    'index' => 'statuscoa',
                ],
                [
                    'label' => 'Status Account Payable',
                    'index' => 'statusaccountpayable',
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
        $cekdata = $akunPusat->cekValidasi($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
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
                'status' => true,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }


    /**
     * @ClassName 
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

                for ($i = 0; $i < count($request->coaId); $i++) {
                    $akunPusat = (new AkunPusat())->findAll($request->coaId[$i]);

                    $transferToCabang = $this->checkToCabang($request->cabang[$x], $akunPusat);

                    if ($transferToCabang['statuscode'] == 200) {
                        if ($transferToCabang['data']['status'] == false) {
                            $messages200[] = $transferToCabang['cabang'] . ' : sudah pernah input' ;
                            $dataCoa[] = $akunPusat->coa;
                        }
                    } else if ($transferToCabang['statuscode'] == 500) {
                        $messages[] = $transferToCabang['cabang'] . ' : server sedang offline';
                    } else {
                        $messages[] = $transferToCabang['cabang'] . ' : proses cek coa belum ada';
                    }
                }
                if ($transferToCabang['statuscode'] == 200) {
                    $data = implode(', ', $dataCoa);
                    $msg = array_unique($messages200);
                    $messages[] = $msg[0].' '.$data;
                }
                // Add the messages to the return array
                if (!empty($messages)) {
                    $messages = array_unique($messages);
                    $returnArray[$x] = implode(', ', $messages);
                }
            }
            if(!empty($returnArray)){
                return response([
                    'data' => $returnArray
                ], 422);
            }

            for ($x = 0; $x < count($request->cabang); $x++) {
                for ($i = 0; $i < count($request->coaId); $i++) {

                    $akunPusat = (new AkunPusat())->findAll($request->coaId[$i]);

                    $transferToCabang = $this->transferToCabang($request->cabang[$x], $akunPusat);

                    $statusCode[] = $transferToCabang['statuscode'];
                }
                if ($transferToCabang['statuscode'] != 200) {
                    $cabangError[] = $transferToCabang['cabang'];
                    $statusCodeError[] = $transferToCabang['statuscode'];
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
                "server" => getenv('MDN_SERVER'),
            ];
        } else if ($getCabang->kodecabang == 'SBY') {
            return [
                "cabang" => $getCabang->namacabang,
                "server" => getenv('SBT_SERVER'),
            ];
        } else if ($getCabang->kodecabang == 'MKS') {
            return [
                "cabang" => $getCabang->namacabang,
                "server" => getenv('MKS_SERVER'),
            ];
        } else if ($getCabang->kodecabang == 'JKT') {
            return [
                "cabang" => $getCabang->namacabang,
                "server" => getenv('JKT_SERVER'),
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
                'user' => getenv('USER_API'),
                'password' => getenv('PASSWORD_API'),
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
                'user' => getenv('USER_API'),
                'password' => getenv('PASSWORD_API'),
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
                'user' => getenv('USER_API'),
                'password' => getenv('PASSWORD_API'),
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
