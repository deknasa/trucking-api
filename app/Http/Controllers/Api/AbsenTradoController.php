<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\AbsenTrado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreAbsenTradoRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\UpdateAbsenTradoRequest;
use App\Http\Requests\DestroyAbsenTradoRequest;
use App\Http\Requests\RangeExportReportRequest;


class AbsenTradoController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $absenTrado = new AbsenTrado();

        return response([
            'data' => $absenTrado->get(),
            'attributes' => [
                'totalRows' => $absenTrado->totalRows,
                'totalPages' => $absenTrado->totalPages
            ]
        ]);
    }

    public function default()
    {
        $absenTrado = new AbsenTrado();
        return response([
            'status' => true,
            'data' => $absenTrado->default()
        ]);
    }

    public function cekValidasi($id)
    {
        $absenTrado = new AbsenTrado();
        $dataMaster = $absenTrado->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $cekdata = $absenTrado->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {
            $keterangan = $error->cekKeteranganError('SATL') ?? '';

            $data = [
                'status' => false,
                'message' => $keterangan. " (".$cekdata['keterangan'].")",
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
                    (new MyModel())->updateEditingBy('absenTrado', $id, $aksi);
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
            (new MyModel())->updateEditingBy('absenTrado', $id, $aksi);
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
    public function store(StoreAbsenTradoRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodeabsen' => $request->kodeabsen ?? '',
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'key' => $request->key ?? '',
                'value' => $request->value ?? ''
            ];
            $absenTrado = (new AbsenTrado())->processStore($data);
            $absenTrado->position = $this->getPosition($absenTrado, $absenTrado->getTable())->position;
            if ($request->limit == 0) {
                $absenTrado->page = ceil($absenTrado->position / (10));
            } else {
                $absenTrado->page = ceil($absenTrado->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $absenTrado
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(AbsenTrado $absentrado)
    {
        return response([
            'status' => true,
            'data' => (new AbsenTrado())->findAll($absentrado->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateAbsenTradoRequest $request, AbsenTrado $absentrado): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodeabsen' => $request->kodeabsen ?? '',
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'key' => $request->key ?? '',
                'value' => $request->value ?? ''
            ];

            $absentrado = (new AbsenTrado())->processUpdate($absentrado, $data);
            $absentrado->position = $this->getPosition($absentrado, $absentrado->getTable())->position;
            if ($request->limit == 0) {
                $absentrado->page = ceil($absentrado->position / (10));
            } else {
                $absentrado->page = ceil($absentrado->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $absentrado
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
    public function destroy(DestroyAbsenTradoRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $absenTrado = (new AbsenTrado())->processDestroy($id);
            $selected = $this->getPosition($absenTrado, $absenTrado->getTable(), true);
            $absenTrado->position = $selected->position;
            $absenTrado->id = $selected->id;
            if ($request->limit == 0) {
                $absenTrado->page = ceil($absenTrado->position / (10));
            } else {
                $absenTrado->page = ceil($absenTrado->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $absenTrado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function addrow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key.*' => 'required',
            'value.*' => 'required',
        ], [
            'key.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'value.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'key' => 'judul',
            'value' => 'keterangan',
            'key.*' => 'judul',
            'value.*' => 'keterangan',
        ]);
        if ($validator->fails()) {

            return response()->json([
                "message" => "The given data was invalid.",
                "errors" => $validator->messages()
            ], 422);
        }
        return true;
    }

    public function detail()
    {
        $query = AbsenTrado::select('memo')->where('id', request()->id)->first();

        $memo = json_decode($query->memo);

        $array = [];
        if ($memo != '') {

            $i = 0;
            foreach ($memo as $index => $value) {
                $array[$i]['key'] = $index;
                $array[$i]['value'] = $value;

                $i++;
            }
        }

        return response([
            'data' => $array
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('absentrado')->getColumns();

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

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $absentrados = $decodedResponse['data'];

            $judulLaporan = $absentrados[0]['judulLaporan'];

            $i = 0;
            foreach ($absentrados as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $absentrados[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Absen',
                    'index' => 'kodeabsen',
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

            $this->toExcel($judulLaporan, $absentrados, $columns);
        }
    }

    public function rekapabsentrado(Request $request)
    {
        $id = $request->absensi_id;
        $absenTrado = new AbsenTrado();
        return response([
            'data' => $absenTrado->getRekapAbsenTrado($id),
        ]);
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
            (new AbsenTrado())->processApprovalnonaktif($data);

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
