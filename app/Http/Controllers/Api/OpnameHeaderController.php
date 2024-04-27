<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use App\Models\OpnameDetail;
use App\Models\OpnameHeader;
use App\Rules\DateTutupBuku;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\LaporanSaldoInventory;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\StoreOpnameHeaderRequest;
use App\Http\Requests\UpdateOpnameHeaderRequest;
use App\Http\Requests\DestroyOpnameHeaderRequest;

class OpnameHeaderController extends Controller
{
    /**
     * @ClassName 
     * OpnameHeaderController
     * @Detail OpnameDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $opnameHeader = new OpnameHeader();
        return response([
            'data' => $opnameHeader->get(),
            'attributes' => [
                'totalRows' => $opnameHeader->totalRows,
                'totalPages' => $opnameHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreOpnameHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $requestData = json_decode($request->detail, true);
            $data = [
                'tglbukti' => $request->tglbukti,
                'keterangan' => $request->keterangan,
                'gudang_id' => $request->gudang_id,
                'kelompok_id' => $request->kelompok_id,
                'stok_id' => $requestData['stok_id'],
                'qty' => $requestData['qty'],
                'tglbuktimasuk' => $requestData['tglbuktimasuk'],
                'qtyfisik' => $requestData['qtyfisik']
            ];
            $opnameHeader = (new OpnameHeader())->processStore($data);
            $opnameHeader->position = $this->getPosition($opnameHeader, $opnameHeader->getTable())->position;
            if ($request->limit == 0) {
                $opnameHeader->page = ceil($opnameHeader->position / (10));
            } else {
                $opnameHeader->page = ceil($opnameHeader->position / ($request->limit ?? 10));
            }
            $opnameHeader->tgldariheader = date('Y-m-01', strtotime($request->tglbukti));
            $opnameHeader->tglsampaiheader = date('Y-m-t', strtotime($request->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $opnameHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        return response([
            'data' => (new OpnameHeader())->findAll($id),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateOpnameHeaderRequest $request, OpnameHeader $opnameheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $requestData = json_decode($request->detail, true);
            $data = [
                'tglbukti' => $request->tglbukti,
                'keterangan' => $request->keterangan,
                'gudang_id' => $request->gudang_id,
                'kelompok_id' => $request->kelompok_id,
                'stok_id' => $requestData['stok_id'],
                'qty' => $requestData['qty'],
                'tglbuktimasuk' => $requestData['tglbuktimasuk'],
                'qtyfisik' => $requestData['qtyfisik']
            ];
            $opnameHeader = (new OpnameHeader())->processUpdate($opnameheader, $data);
            $opnameHeader->position = $this->getPosition($opnameHeader, $opnameHeader->getTable())->position;
            if ($request->limit == 0) {
                $opnameHeader->page = ceil($opnameHeader->position / (10));
            } else {
                $opnameHeader->page = ceil($opnameHeader->position / ($request->limit ?? 10));
            }
            $opnameHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $opnameHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $opnameHeader
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
    public function destroy(DestroyOpnameHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $opnameHeader = (new OpnameHeader())->processDestroy($id, 'DELETE OPNAME');
            $selected = $this->getPosition($opnameHeader, $opnameHeader->getTable(), true);
            $opnameHeader->position = $selected->position;
            $opnameHeader->id = $selected->id;
            if ($request->limit == 0) {
                $opnameHeader->page = ceil($opnameHeader->position / (10));
            } else {
                $opnameHeader->page = ceil($opnameHeader->position / ($request->limit ?? 10));
            }
            $opnameHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $opnameHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $opnameHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $opname = OpnameHeader::find($id);
        $statusdatacetak = $opname->statuscetak;
        $statusdataApproval = $opname->statusapproval;

        $statusCetak = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
        ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        $aksi = request()->aksi ?? '';
        if ($statusdataApproval == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'error' => true,

                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } 
        if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();

            $data = [
                'error' => true,
                'message' =>  'No Bukti ' . $opname->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
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
    public function export($id)
    {
        $opnameHeader = new OpnameHeader();
        return response([
            'data' => $opnameHeader->getExport($id)
        ]);
    }

    public function getStok(Request $request)
    {

        $getFilter = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();
        $getJenisTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'LAPORAN STOK INVENTORI')->where('text', 'TANGGAL MASUK GUDANG KANTOR')->first();
        $kelompok_id = 0;
        $statusreuse = 0;
        $statusban = 0;
        $filter = $getFilter->id;
        $jenistgltampil = $getJenisTgl->id;
        $priode = date('d-m-Y');
        $stokdari_id = 0;
        $stoksampai_id = 0;
        $dataFilter = $request->gudang_id;
        $kelompok = $request->kelompok_id??0;
        $report = (new OpnameHeader())->getInventory($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter,1,$kelompok);

        return response([
            'data' => $report
        ]);
    }

    public function getEdit($id)
    {
        return response([
            'data' => (new OpnameDetail())->findAll($id)
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $opnameHeader = OpnameHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($opnameHeader->statuscetak != $statusSudahCetak->id) {
                $opnameHeader->statuscetak = $statusSudahCetak->id;
                $opnameHeader->tglbukacetak = date('Y-m-d H:i:s');
                $opnameHeader->userbukacetak = auth('api')->user()->name;
                if ($opnameHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($opnameHeader->getTable()),
                        'postingdari' => 'PRINT OPNAME HEADER',
                        'idtrans' => $opnameHeader->id,
                        'nobuktitrans' => $opnameHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $opnameHeader->toArray(),
                        'modifiedby' => $opnameHeader->modifiedby
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

    // /**
    //  * @ClassName
    //  * @Keterangan APPROVAL DATA
    //  */
    // public function approval(OpnameHeader $id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $opnameHeader =$id;
    //         $opnameHeader = (new OpnameHeader())->processApprove($opnameHeader);

    //         DB::commit();
    //         return response([
    //             'message' => 'Berhasil'
    //         ]);
    //     } catch (\Throwable $th) {
    //         throw $th;
    //     }
    // }
    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->Id as $id) {
                $opnameHeader =OpnameHeader::find($id);
                $opnameHeader = (new OpnameHeader())->processApprove($opnameHeader);
            }

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    
}
