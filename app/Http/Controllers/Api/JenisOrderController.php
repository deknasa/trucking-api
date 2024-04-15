<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;

use App\Models\JenisOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJenisOrderRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\UpdateJenisOrderRequest;
use App\Http\Requests\RangeExportReportRequest;

class JenisOrderController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $jenisorder = new JenisOrder();

        return response([
            'data' => $jenisorder->get(),
            'attributes' => [
                'totalRows' => $jenisorder->totalRows,
                'totalPages' => $jenisorder->totalPages
            ]
        ]);
    }
    public function cekValidasi($id)
    {
        $jenisOrder = new JenisOrder();
        $dataMaster = $jenisOrder->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $cekdata = $jenisOrder->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {
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
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('jenisOrder', $id, $aksi);
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
            (new MyModel())->updateEditingBy('jenisOrder', $id, $aksi);
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
        $jenisOrder = new JenisOrder();
        return response([
            'status' => true,
            'data' => $jenisOrder->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreJenisOrderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodejenisorder' => $request->kodejenisorder ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'keterangan' => $request->keterangan ?? ''

            ];
            $jenisorder = (new JenisOrder())->processStore($data);
            $jenisorder->position = $this->getPosition($jenisorder, $jenisorder->getTable())->position;
            if ($request->limit==0) {
                $jenisorder->page = ceil($jenisorder->position / (10));
            } else {
                $jenisorder->page = ceil($jenisorder->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenisorder
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(JenisOrder $jenisorder)
    {
        return response([
            'status' => true,
            'data' => (new JenisOrder())->findAll($jenisorder->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateJenisOrderRequest $request, JenisOrder $jenisorder): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodejenisorder' => $request->kodejenisorder ?? '',
                'statusaktif' => $request->statusaktif ?? '',
                'keterangan' => $request->keterangan ?? ''
            ];

            $jenisorder = (new JenisOrder())->processUpdate($jenisorder, $data);
            $jenisorder->position = $this->getPosition($jenisorder, $jenisorder->getTable())->position;
            if ($request->limit==0) {
                $jenisorder->page = ceil($jenisorder->position / (10));
            } else {
                $jenisorder->page = ceil($jenisorder->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $jenisorder
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
            $jenisorder = (new JenisOrder())->processDestroy($id);
            $selected = $this->getPosition($jenisorder, $jenisorder->getTable(), true);
            $jenisorder->position = $selected->position;
            $jenisorder->id = $selected->id;
            if ($request->limit==0) {
                $jenisorder->page = ceil($jenisorder->position / (10));
            } else {
                $jenisorder->page = ceil($jenisorder->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $jenisorder
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenisorder')->getColumns();

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
            $jenisorders = $decodedResponse['data'];

            $judulLaporan = $jenisorders[0]['judulLaporan'];

            $i = 0;
            foreach ($jenisorders as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $jenisorders[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Jenis Order',
                    'index' => 'kodejenisorder',
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

            $this->toExcel($judulLaporan, $jenisorders, $columns);
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
            (new JenisOrder())->processApprovalnonaktif($data);

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
