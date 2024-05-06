<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\DestroyTripTangkiRequest;
use App\Http\Requests\StoreTripTangkiRequest;
use App\Http\Requests\UpdateTripTangkiRequest;
use App\Models\TripTangki;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class TripTangkiController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $triptangki = new TripTangki();

        return response([
            'data' => $triptangki->get(),
            'attributes' => [
                'totalRows' => $triptangki->totalRows,
                'totalPages' => $triptangki->totalPages
            ]
        ]);
    }

    public function default()
    {
        $triptangki = new TripTangki();
        return response([
            'status' => true,
            'data' => $triptangki->default()
        ]);
    }

    public function cekValidasi($id)
    {
        $triptangki = new TripTangki();
        $dataMaster = $triptangki->where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $cekdata = $triptangki->cekvalidasihapus($id);
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
                    (new MyModel())->updateEditingBy('triptangki', $id, $aksi);
                }

                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => false,
                    'editblok' => false,
                ];

                // return response($data);
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
            (new MyModel())->updateEditingBy('triptangki', $id, $aksi);
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTripTangkiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodetangki' => $request->kodetangki ?? '',
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'tas_id' => $request->tas_id ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $triptangki = (new TripTangki())->processStore($data);

            if ($request->from == '') {
                $triptangki->position = $this->getPosition($triptangki, $triptangki->getTable())->position;
                if ($request->limit == 0) {
                    $triptangki->page = ceil($triptangki->position / (10));
                } else {
                    $triptangki->page = ceil($triptangki->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $triptangki->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('triptangki', 'add', $data);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $triptangki
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(TripTangki $triptangki)
    {
        return response([
            'status' => true,
            'data' => $triptangki
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTripTangkiRequest $request, TripTangki $triptangki): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodetangki' => $request->kodetangki ?? '',
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];

            $triptangki = (new TripTangki())->processUpdate($triptangki, $data);
            if ($request->from == '') {
                $triptangki->position = $this->getPosition($triptangki, $triptangki->getTable())->position;
                if ($request->limit == 0) {
                    $triptangki->page = ceil($triptangki->position / (10));
                } else {
                    $triptangki->page = ceil($triptangki->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $triptangki->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('trpi$triptangki', 'edit', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $triptangki
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
    public function destroy(DestroyTripTangkiRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $triptangki = (new TripTangki())->processDestroy($id);

            if ($request->from == '') {
                $selected = $this->getPosition($triptangki, $triptangki->getTable(), true);
                $triptangki->position = $selected->position;
                $triptangki->id = $selected->id;
                if ($request->limit == 0) {
                    $triptangki->page = ceil($triptangki->position / (10));
                } else {
                    $triptangki->page = ceil($triptangki->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('triptangki', 'delete', $data);
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $triptangki
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('triptangki')->getColumns();

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
    public function export()
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
            $triptangkis = $decodedResponse['data'];

            $judulLaporan = $triptangkis[0]['judulLaporan'];

            $i = 0;
            foreach ($triptangkis as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $triptangkis[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Tangki',
                    'index' => 'kodetangki',
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

            $this->toExcel($judulLaporan, $triptangkis, $columns);
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
            (new TripTangki())->processApprovalnonaktif($data);

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
