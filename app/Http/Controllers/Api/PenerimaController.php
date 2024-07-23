<?php

namespace App\Http\Controllers\Api;

use App\Models\Penerima;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaRequest;
use App\Http\Requests\UpdatePenerimaRequest;
use App\Http\Requests\DestroyPenerimaRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\MyModel;
use App\Models\Error;
use DateTime;


class PenerimaController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $penerima = new Penerima();

        return response([
            'data' => $penerima->get(),
            'attributes' => [
                'totalRows' => $penerima->totalRows,
                'totalPages' => $penerima->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $penerima = new Penerima();
        $cekdata = $penerima->cekvalidasihapus($id);
        $dataMaster = Penerima::where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
      
        $aksi = request()->aksi ?? '';
        $aksi =strtoupper($aksi);
        if( $aksi == 'EDIT'){
            $cekdata['kondisi'] = false;
        }
        if ($useredit != '' && $useredit != $user) {
           
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('penerima', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->namapenerima . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
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
            $keterror = 'Data <b>' . $dataMaster->namapenerima . '</b><br>' . $keteranganerror . ' <b> <br> ' . $keterangantambahanerror;

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
                (new MyModel())->updateEditingBy('penerima', $id, $aksi);
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
        $penerima = new Penerima();
        return response([
            'status' => true,
            'data' => $penerima->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePenerimaRequest $request): JsonResponse
    {

        DB::beginTransaction();
        // dd($request->npwp);
        try {
            $data = [
                'namapenerima' => $request->namapenerima,
                'npwp' => $request->npwp ?? '',
                'noktp' => $request->noktp ?? '',
                'keterangan' => $request->keterangan,
                'statusaktif' => $request->statusaktif,
                'statuskaryawan' => $request->statuskaryawan,
            ];
            // $penerima = (new Penerima())->processStore($data);
            $penerima = new Penerima();
            $penerima->processStore($data, $penerima);

            if ($request->from == '') {
            
            $penerima->position = $this->getPosition($penerima, $penerima->getTable())->position;
            if ($request->limit==0) {
                $penerima->page = ceil($penerima->position / (10));
            } else {
                $penerima->page = ceil($penerima->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $penerima->id;

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('penerima', 'add', $data);
            $this->SaveTnlNew('penerima', 'add', $data);
        }
    
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerima
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $penerima = new Penerima();
        return response([
            'status' => true,
            'data' => $penerima->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePenerimaRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'namapenerima' => $request->namapenerima,
                'npwp' => $request->npwp ?? '',
                'noktp' => $request->noktp ?? '',
                'keterangan' => $request->keterangan,
                'statusaktif' => $request->statusaktif,
                'statuskaryawan' => $request->statuskaryawan,
            ];

            // $penerima = (new Penerima())->processUpdate($penerima, $data);
            $penerima = new Penerima();
            $penerimas = $penerima->findOrFail($id);
            $penerima = $penerima->processUpdate($penerimas, $data);            
            if ($request->from == '') {

            $penerima->position = $this->getPosition($penerima, $penerima->getTable())->position;
           if ($request->limit==0) {
                $penerima->page = ceil($penerima->position / (10));
            } else {
                $penerima->page = ceil($penerima->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $penerima->id;

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('penerima', 'edit', $data);
            $this->SaveTnlNew('penerima', 'edit', $data);
        }        
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $penerima
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
    public function destroy(DestroyPenerimaRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $penerima = new Penerima();
            $penerimas = $penerima->findOrFail($id);
            $penerima = $penerima->processDestroy($penerimas);


            // $penerima = (new Penerima())->processDestroy($id);
            if ($request->from == '') {

            $selected = $this->getPosition($penerima, $penerima->getTable(), true);
            $penerima->position = $selected->position;
            $penerima->id = $selected->id;
           if ($request->limit==0) {
                $penerima->page = ceil($penerima->position / (10));
            } else {
                $penerima->page = ceil($penerima->position / ($request->limit ?? 10));
            }
        }
        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $id;

        $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('penerima', 'delete', $data);
            $this->SaveTnlNew('penerima', 'delete', $data);
        }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerima
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
            (new Penerima())->processApprovalnonaktif($data);

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
            (new Penerima())->processApprovalaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
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
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerima')->getColumns();

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
            $penerimas = $decodedResponse['data'];

            $judulLaporan = $penerimas[0]['judulLaporan'];

            $i = 0;
            foreach ($penerimas as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusKaryawan = $params['statuskaryawan'];

                $result = json_decode($statusaktif, true);
                $resultKaryawan = json_decode($statusKaryawan, true);

                $statusaktif = $result['MEMO'];
                $statusKaryawan = $resultKaryawan['MEMO'];

                $penerimas[$i]['statusaktif'] = $statusaktif;
                $penerimas[$i]['statuskaryawan'] = $statusKaryawan;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Nama Penerima',
                    'index' => 'namapenerima',
                ],
                [
                    'label' => 'NPWP',
                    'index' => 'npwp',
                ],
                [
                    'label' => 'No KTP',
                    'index' => 'noktp',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Status Karyawan',
                    'index' => 'statuskaryawan',
                ],
            ];

            $this->toExcel($judulLaporan, $penerimas, $columns);
        }
    }
}
