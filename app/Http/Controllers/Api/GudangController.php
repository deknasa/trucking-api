<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Models\Stok;
use App\Models\Error;
use App\Models\Gudang;
use App\Models\MyModel;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreGudangRequest;
use App\Http\Requests\UpdateGudangRequest;
use App\Http\Requests\DestroyGudangRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;

class GudangController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $gudang = new Gudang();
        return response([
            'data' => $gudang->get(),
            'attributes' => [
                'totalRows' => $gudang->totalRows,
                'totalPages' => $gudang->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {

        $gudang = new Gudang();
        $dataMaster = $gudang->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $cekdata = $gudang->cekvalidasihapus($id);

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
                    (new MyModel())->updateEditingBy('gudang', $id, $aksi);
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
                $keterror = 'Data <b>' . $dataMaster->gudang . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                
                $data = [
                    'status' => true,
                    'message' => ["keterangan"=>$keterror],
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];
                
                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('gudang', $id, $aksi);

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
        $gudang = new Gudang();
        return response([
            'status' => true,
            'data' => $gudang->default()
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreGudangRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'gudang' => $request->gudang,
                'statusaktif' => $request->statusaktif
            ];
            $gudang = (new Gudang())->processStore($data);
            $selected = $this->getPosition($gudang, $gudang->getTable());
            $gudang->position = $selected->position;
           if ($request->limit==0) {
                $gudang->page = ceil($gudang->position / (10));
            } else {
                $gudang->page = ceil($gudang->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gudang
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Gudang $gudang)
    {
        return response([
            'status' => true,
            'data' => (new Gudang())->findAll($gudang->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateGudangRequest $request, Gudang $gudang): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'gudang' => $request->gudang ?? '',
                'statusaktif' => $request->statusaktif
            ];

            $gudang = (new Gudang())->processUpdate($gudang, $data);
            $gudang->position = $this->getPosition($gudang, $gudang->getTable())->position;
           if ($request->limit==0) {
                $gudang->page = ceil($gudang->position / (10));
            } else {
                $gudang->page = ceil($gudang->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $gudang
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
    public function destroy(DestroyGudangRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $gudang = (new Gudang())->processDestroy($id);
            $selected = $this->getPosition($gudang, $gudang->getTable(), true);
            $gudang->position = $selected->position;
            $gudang->id = $selected->id;
           if ($request->limit==0) {
                $gudang->page = ceil($gudang->position / (10));
            } else {
                $gudang->page = ceil($gudang->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $gudang
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
            (new Gudang())->processApprovalnonaktif($data);

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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gudang')->getColumns();

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
            'statusgudang' => Parameter::where(['grp' => 'status gudang'])->get(),
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
            $gudangs = $decodedResponse['data'];

            $judulLaporan = $gudangs[0]['judulLaporan'];

            $i = 0;
            foreach ($gudangs as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $gudangs[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Gudang',
                    'index' => 'gudang',
                ],
                [
                    'label' => 'Status',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $gudangs, $columns);
        }
    }
}
