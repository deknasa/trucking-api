<?php

namespace App\Http\Controllers\Api;

use App\Models\MyModel;
use DateTime;
use App\Models\Bank;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\Error;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\StoreBankRequest;
use Illuminate\Database\QueryException;
use App\Http\Requests\UpdateBankRequest;
use App\Http\Requests\DestroyBankRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;

class BankController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $bank = new Bank();
        return response([
            'data' => $bank->get(),
            'attributes' => [
                'totalRows' => $bank->totalRows,
                'totalPages' => $bank->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $bank = new Bank();
        $cekdata = $bank->cekvalidasihapus($id);
        
        $dataMaster = Bank::where('id',$id)->first();
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

                    (new MyModel())->updateEditingBy('bank', $id, $aksi);
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
            $keterror = 'Data <b>' . $dataMaster->kodebank . '</b><br>' . $keteranganerror . ' <b> <br> ' . $keterangantambahanerror;

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
                (new MyModel())->updateEditingBy('bank', $id, $aksi);
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
        $bank = new Bank();
        return response([
            'status' => true,
            'data' => $bank->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreBankRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodebank' => $request->kodebank,
                'namabank' => $request->namabank,
                'coa' => $request->coa,
                'tipe' => $request->tipe,
                'statusaktif' => $request->statusaktif,
                'formatpenerimaan' => $request->formatpenerimaan,
                'formatpengeluaran' => $request->formatpengeluaran,
                'formatcetakan' => $request->formatcetakan,
            ];

            $bank = (new Bank())->processStore($data);
            $bank->position = $this->getPosition($bank, $bank->getTable())->position;
            if ($request->limit==0) {
                $bank->page = ceil($bank->position / (10));
            } else {
                $bank->page = ceil($bank->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bank,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(Bank $bank)
    {
        return response([
            'status' => true,
            'data' => $bank
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateBankRequest $request, Bank $bank): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodebank' => $request->kodebank,
                'namabank' => $request->namabank,
                'coa' => $request->coa,
                'tipe' => $request->tipe,
                'statusaktif' => $request->statusaktif,
                'formatpenerimaan' => $request->formatpenerimaan,
                'formatpengeluaran' => $request->formatpengeluaran,
                'formatcetakan' => $request->formatcetakan,
            ];

            $bank = (new Bank())->processUpdate($bank, $data);
            $bank->position = $this->getPosition($bank, $bank->getTable())->position;
            if ($request->limit==0) {
                $bank->page = ceil($bank->position / (10));
            } else {
                $bank->page = ceil($bank->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $bank
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
    public function destroy(DestroyBankRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $bank = (new Bank())->processDestroy($id);
            $selected = $this->getPosition($bank, $bank->getTable(), true);
            $bank->position = $selected->position;
            $bank->id = $selected->id;
            if ($request->limit==0) {
                $bank->page = ceil($bank->position / (10));
            } else {
                $bank->page = ceil($bank->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $bank
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
            (new Bank())->processApprovalnonaktif($data);

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
            'status' => Parameter::where(['grp' => 'status aktif'])->get(),

        ];

        return response([
            'data' => $data
        ]);
    }



    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('bank')->getColumns();

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
            $banks = $decodedResponse['data'];

            $judulLaporan = $banks[0]['judulLaporan'];

            $i = 0;
            foreach ($banks as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusDefault = $params['statusdefault'];
                $formatPenerimaan = $params['formatpenerimaan'];
                $formatPengeluaran = $params['formatpengeluaran'];

                $result = json_decode($statusaktif, true);
                $resultDefault = json_decode($statusDefault, true);
                $resultPengeluaran = json_decode($formatPengeluaran, true);
                $resultPenerimaan = json_decode($formatPenerimaan, true);

                $statusaktif = $result['MEMO'];
                $statusDefault = $resultDefault['MEMO'];
                $formatPenerimaan = $resultPengeluaran['MEMO'];
                $formatPengeluaran = $resultPenerimaan['MEMO'];


                $banks[$i]['statusaktif'] = $statusaktif;
                $banks[$i]['statusdefault'] = $statusDefault;
                $banks[$i]['formatpenerimaan'] = $formatPenerimaan;
                $banks[$i]['formatpengeluaran'] = $formatPengeluaran;


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
                    'label' => 'Kode Perkiraan',
                    'index' => 'coa',
                ],
                [
                    'label' => 'Tipe',
                    'index' => 'tipe',
                ],
                [
                    'label' => 'Status Default',
                    'index' => 'statusdefault',
                ],
                [
                    'label' => 'Format Penerimaan',
                    'index' => 'formatpenerimaan',
                ],
                [
                    'label' => 'Format Pengeluaran',
                    'index' => 'formatpengeluaran',
                ],
                [
                    'label' => 'Status AKtif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $banks, $columns);
        }
    }
}
