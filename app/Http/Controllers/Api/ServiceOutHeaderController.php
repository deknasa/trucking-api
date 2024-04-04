<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\ServiceInHeader;
use App\Models\ServiceOutDetail;
use App\Models\ServiceOutHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreServiceOutDetailRequest;
use App\Http\Requests\StoreServiceOutHeaderRequest;
use App\Http\Requests\UpdateServiceOutHeaderRequest;
use App\Http\Requests\DestroyServiceOutHeaderRequest;

class ServiceOutHeaderController extends Controller
{

    /**
     * @ClassName 
     * ServiceOutHeader
     * @Detail ServiceOutDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $serviceout = new ServiceOutHeader();

        return response([
            'data' => $serviceout->get(),
            'attributes' => [
                'totalRows' => $serviceout->totalRows,
                'totalPages' => $serviceout->totalPages
            ]
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreServiceOutHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'trado_id' => $request->trado_id,
                'tglkeluar' => $request->tglkeluar,
                'servicein_nobukti' => $request->servicein_nobukti,
                'keterangan_detail' => $request->keterangan_detail
            ];

            $serviceOutHeader = (new ServiceOutHeader())->processStore($data);
            $serviceOutHeader->position = $this->getPosition($serviceOutHeader, $serviceOutHeader->getTable())->position;
            if ($request->limit==0) {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / (10));
            } else {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / ($request->limit ?? 10));
            }
            $serviceOutHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $serviceOutHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $serviceOutHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {

        $data = ServiceOutHeader::findAll($id);
        $detail = ServiceOutDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateServiceOutHeaderRequest $request, ServiceOutHeader $serviceoutheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '',
                'trado_id' => $request->trado_id ?? 0,
                'tglkeluar' =>  $request->tglkeluar,
                'servicein_nobukti' => $request->servicein_nobukti ?? '',
                'keterangan_detail' => $request->keterangan_detail ?? ''
            ];

            $serviceOutHeader = (new ServiceOutHeader())->processUpdate($serviceoutheader, $data);
            $serviceOutHeader->position = $this->getPosition($serviceOutHeader, $serviceOutHeader->getTable())->position;
            if ($request->limit==0) {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / (10));
            } else {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / ($request->limit ?? 10));
            }
            $serviceOutHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $serviceOutHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $serviceoutheader
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
    public function destroy(DestroyServiceOutHeaderRequest $request, $id)
    {

        DB::beginTransaction();
        try {
            $serviceOutHeader = (new ServiceOutHeader())->processDestroy($id);
            $selected = $this->getPosition($serviceOutHeader, $serviceOutHeader->getTable(), true);
            $serviceOutHeader->position = $selected->position;
            $serviceOutHeader->id = $selected->id;
            if ($request->limit==0) {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / (10));
            } else {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / ($request->limit ?? 10));
            }
            $serviceOutHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $serviceOutHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $serviceOutHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function cekvalidasi($id)
    {
        $pengeluaran = ServiceOutHeader::findOrFail($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        // if ($status == $statusApproval->id) {
        //     $query = Error::from(DB::raw("error with (readuncommitted)"))
        //         ->select('keterangan')
        //         ->whereRaw("kodeerror = 'SAP'")
        //         ->get();
        //     $keterangan = $query['0'];
        //     $data = [
        //         'message' => $keterangan,
        //         'errors' => 'sudah approve',
        //         'kodestatus' => '1',
        //         'kodenobukti' => '1'
        //     ];

        //     return response($data);
        // } else 
        $aksi = request()->aksi ?? '';
        $user = auth('api')->user()->name;
        $useredit = $pengeluaran->editing_by ?? '';
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        if ($statusdatacetak == $statusCetak->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SDC'")
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
         } else if ($useredit != '' && $useredit != $user) {
           
            $waktu = (new Parameter())->cekBatasWaktuEdit('Service Out Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($pengeluaran->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('ServiceOutHeader', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $pengeluaran->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => ["keterangan"=>$keterror],
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            
        } else {
            (new MyModel())->updateEditingBy('ServiceOutHeader', $id, $aksi);


            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];

            return response($data);
        }
    }
    public function combo(Request $request)
    {
        $data = [
            'mekanik' => Mekanik::all(),
            'trado' => Trado::all(),
            'serviceout' => ServiceOutDetail::all(),
            'servicein' => ServiceInHeader::all()
        ];

        return response([
            'data' => $data
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('serviceoutheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $serviceOutHeader = ServiceOutHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($serviceOutHeader->statuscetak != $statusSudahCetak->id) {
                $serviceOutHeader->statuscetak = $statusSudahCetak->id;
                $serviceOutHeader->tglbukacetak = date('Y-m-d H:i:s');
                $serviceOutHeader->userbukacetak = auth('api')->user()->name;
                $serviceOutHeader->jumlahcetak = $serviceOutHeader->jumlahcetak + 1;
                if ($serviceOutHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($serviceOutHeader->getTable()),
                        'postingdari' => 'PRINT SERVICE OUT HEADER',
                        'idtrans' => $serviceOutHeader->id,
                        'nobuktitrans' => $serviceOutHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $serviceOutHeader->toArray(),
                        'modifiedby' => $serviceOutHeader->modifiedby
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    DB::commit();
                }
            }
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
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
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }

        /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $serviceOut = new ServiceOutHeader();
        return response([
            'data' => $serviceOut->getExport($id)
        ]);
    }
}
