<?php

namespace App\Http\Controllers\Api;
use DateTime;
use App\Models\Bank;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\AlatBayar;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\MyModel;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreAlatBayarRequest;
use App\Http\Requests\UpdateAlatBayarRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\DestroyAlatBayarRequest;
use App\Http\Requests\RangeExportReportRequest;

class AlatBayarController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $alatbayar = new AlatBayar();

        return response([
            'data' => $alatbayar->get(),
            'attributes' => [
                'totalRows' => $alatbayar->totalRows,
                'totalPages' => $alatbayar->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $alatBayar = new AlatBayar();
        $cekdata = $alatBayar->cekvalidasihapus($id);
        $dataMaster = AlatBayar::where('id',$id)->first();
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

                    (new MyModel())->updateEditingBy('alatbayar', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->kodealatbayar . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
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
            $keterror = 'Data <b>' . $dataMaster->kodealatbayar . '</b><br>' . $keteranganerror . ' <b> <br> ' . $keterangantambahanerror;

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
                (new MyModel())->updateEditingBy('alatbayar', $id, $aksi);
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

    public function default()
    {
        $alatBayar = new AlatBayar();
        return response([
            'status' => true,
            'data' => $alatBayar->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreAlatBayarRequest $request): JsonResponse
    {
        DB::beginTransaction();
        // dd($request->all());
        try {
            $data = [
                'kodealatbayar' => $request->kodealatbayar,
                'namaalatbayar' => $request->namaalatbayar,
                'keterangan' => $request->keterangan ?? '',
                'statuslangsungcair' => $request->statuslangsungcair,
                'statusdefault' => $request->statusdefault,
                'bank_id' => $request->bank_id,
                'coa' => $request->coa ?? '',
                'statusaktif' => $request->statusaktif,
            ];

            $alatbayar = (new AlatBayar())->processStore($data);
            $alatbayar->position = $this->getPosition($alatbayar, $alatbayar->getTable())->position;
            if ($request->limit==0) {
                $alatbayar->page = ceil($alatbayar->position / (10));
            } else {
                $alatbayar->page = ceil($alatbayar->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $alatbayar
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $data = AlatBayar::find($id);
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateAlatBayarRequest $request, AlatBayar $alatbayar): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodealatbayar' => $request->kodealatbayar,
                'namaalatbayar' => $request->namaalatbayar,
                'keterangan' => $request->keterangan ?? '',
                'statuslangsungcair' => $request->statuslangsungcair,
                'statusdefault' => $request->statusdefault,
                'bank_id' => $request->bank_id,
                'coa' => $request->coa ?? '',
                'statusaktif' => $request->statusaktif,
            ];
            $alatbayar = (new AlatBayar())->processUpdate($alatbayar, $data);
            $alatbayar->position = $this->getPosition($alatbayar, $alatbayar->getTable())->position;
            if ($request->limit==0) {
                $alatbayar->page = ceil($alatbayar->position / (10));
            } else {
                $alatbayar->page = ceil($alatbayar->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $alatbayar
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
    public function destroy(DestroyAlatBayarRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $alatbayar = (new AlatBayar())->processDestroy($id);
            $selected = $this->getPosition($alatbayar, $alatbayar->getTable(), true);
            $alatbayar->position = $selected->position;
            $alatbayar->id = $selected->id;
            if ($request->limit==0) {
                $alatbayar->page = ceil($alatbayar->position / (10));
            } else {
                $alatbayar->page = ceil($alatbayar->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $alatbayar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'langsungcair' => Parameter::where(['grp' => 'status langsung cair'])->get(),
            'statusdefault' => Parameter::where(['grp' => 'status default'])->get(),
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
            'bank' => Bank::all(),
        ];

        return response([
            'data' => $data
        ]);
    }


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('alatbayar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
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
            (new AlatBayar())->processApprovalnonaktif($data);

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
            $alatbayars = $decodedResponse['data'];

            $judulLaporan = $alatbayars[0]['judulLaporan'];

            $i = 0;
            foreach ($alatbayars as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusLangsungCair = $params['statuslangsungcair'];
                $statusDefault = $params['statusdefault'];

                $result = json_decode($statusaktif, true);
                $resultLangsungCair = json_decode($statusLangsungCair, true);
                $resultDefault = json_decode($statusDefault, true);

                $statusaktif = $result['MEMO'];
                $statusLangsungCair = $resultLangsungCair['MEMO'];
                $statusDefault = $resultDefault['MEMO'];


                $alatbayars[$i]['statusaktif'] = $statusaktif;
                $alatbayars[$i]['statuslangsungcair'] = $statusLangsungCair;
                $alatbayars[$i]['statusdefault'] = $statusDefault;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Alat Bayar',
                    'index' => 'kodealatbayar',
                ],
                [
                    'label' => 'Nama Alat Bayar',
                    'index' => 'namaalatbayar',
                ],
                [
                    'label' => 'Status Langsung Cair',
                    'index' => 'statuslangsungcair',
                ],
                [
                    'label' => 'Status Default',
                    'index' => 'statusdefault',
                ],
                [
                    'label' => 'Bank',
                    'index' => 'bank',
                ],
                [
                    'label' => 'Status AKtif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
            ];

            $this->toExcel($judulLaporan, $alatbayars, $columns);
        }
    }
}
