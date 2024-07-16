<?php

namespace App\Http\Controllers\Api;

use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\BankPelanggan;
use DateTime;
use App\Models\MyModel;
use App\Models\Error;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreBankPelangganRequest;
use App\Http\Requests\UpdateBankPelangganRequest;
use App\Http\Requests\DestroyBankPelangganRequest;

class BankPelangganController extends Controller
{

   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $bankpelanggan = new BankPelanggan();

        return response([
            'data' => $bankpelanggan->get(),
            'attributes' => [
                'totalRows' => $bankpelanggan->totalRows,
                'totalPages' => $bankpelanggan->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $bankpelanggan = new BankPelanggan();
        $cekdata = $bankpelanggan->cekvalidasihapus($id);
        $aksi = request()->aksi ?? '';
        $aksi =strtoupper($aksi);
        if( $aksi == 'EDIT'){
            $cekdata['kondisi'] = false;
        }
        $dataMaster = BankPelanggan::where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
      
        

        if ($useredit != '' && $useredit != $user) {
           
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('bankpelanggan', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->kodebank . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
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
            $keterror = 'Data <b>' . $dataMaster->kodebank . '</b><br>' . $keteranganerror . ' ( '.$cekdata['keterangan'] .' <b> <br> ' . $keterangantambahanerror;

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
                (new MyModel())->updateEditingBy('bankpelanggan', $id, $aksi);
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
        $bankPelanggan = new BankPelanggan();
        return response([
            'status' => true,
            'data' => $bankPelanggan->default()
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreBankPelangganRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodebank' => $request->kodebank,
                'namabank' => $request->namabank,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
            ];
            // $bankpelanggan = (new BankPelanggan())->processStore($data);
            $bankpelanggan = new BankPelanggan();
            $bankpelanggan->processStore($data, $bankpelanggan);            
            if ($request->from == '') {            
            $bankpelanggan->position = $this->getPosition($bankpelanggan, $bankpelanggan->getTable())->position;
            if ($request->limit==0) {
                $bankpelanggan->page = ceil($bankpelanggan->position / (10));
            } else {
                $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $bankpelanggan->id;

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('bankpelanggan', 'add', $data);
            $this->SaveTnlNew('bankpelanggan', 'add', $data);
        }        
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bankpelanggan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $bankpelanggan = new BankPelanggan();
        return response([
            'status' => true,
            'data' => $bankpelanggan->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateBankPelangganRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodebank' => $request->kodebank,
                'namabank' => $request->namabank,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
            ];

            // $bankpelanggan = (new BankPelanggan())->processUpdate($bankpelanggan, $data);
            $bankpelanggan = new BankPelanggan();
            $bankpelanggans = $bankpelanggan->findOrFail($id);
            $bankpelanggan = $bankpelanggan->processUpdate($bankpelanggans, $data);

            if ($request->from == '') {

            $bankpelanggan->position = $this->getPosition($bankpelanggan, $bankpelanggan->getTable())->position;
            if ($request->limit==0) {
                $bankpelanggan->page = ceil($bankpelanggan->position / (10));
            } else {
                $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $bankpelanggan->id;

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('bankpelanggan', 'edit', $data);
            $this->SaveTnlNew('bankpelanggan', 'edit', $data);
        }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $bankpelanggan
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
    public function destroy(DestroyBankPelangganRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            // $bankpelanggan = (new BankPelanggan())->processDestroy($id);
            $bankpelanggan = new BankPelanggan();
            $bankpelanggans = $bankpelanggan->findOrFail($id);
            $bankpelanggan = $bankpelanggan->processDestroy($bankpelanggans);


            if ($request->from == '') {            
            $selected = $this->getPosition($bankpelanggan, $bankpelanggan->getTable(), true);
            $bankpelanggan->position = $selected->position;
            $bankpelanggan->id = $selected->id;
            if ($request->limit==0) {
                $bankpelanggan->page = ceil($bankpelanggan->position / (10));
            } else {
                $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10));
            }
        }

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        $data['tas_id'] = $id;

        $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            // $this->saveToTnl('bankpelanggan', 'delete', $data);
            $this->SaveTnlNew('bankpelanggan', 'delete', $data);
        }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $bankpelanggan
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
            (new BankPelanggan())->processApprovalnonaktif($data);

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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('bankpelanggan')->getColumns();

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
            $bankpelanggans = $decodedResponse['data'];

            $judulLaporan = $bankpelanggans[0]['judulLaporan'];

            $i = 0;
            foreach ($bankpelanggans as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];

                $bankpelanggans[$i]['statusaktif'] = $statusaktif;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Bank',
                    'index' => 'kodebank',
                ],
                [
                    'label' => 'Nama Bank',
                    'index' => 'namabank',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Status AKtif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $bankpelanggans, $columns);
        }
    }
}
