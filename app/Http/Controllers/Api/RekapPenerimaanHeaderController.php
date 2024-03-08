<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRekapPenerimaanRequest;
use App\Http\Requests\DestroyRekapPenerimaanHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\RekapPenerimaanHeader;
use App\Http\Requests\StoreRekapPenerimaanHeaderRequest;
use App\Http\Requests\UpdateRekapPenerimaanHeaderRequest;

use App\Models\RekapPenerimaanDetail;
use App\Models\PenerimaanHeader;
use App\Models\Error;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreRekapPenerimaanDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use Illuminate\Http\JsonResponse;

class RekapPenerimaanHeaderController extends Controller
{
    /**
     * @ClassName 
     * RekapPenerimaanHeader
     * @Detail RekapPenerimaanDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $rekapPenerimaanHeader = new RekapPenerimaanHeader();
        return response([
            'data' => $rekapPenerimaanHeader->get(),
            'attributes' => [
                'totalRows' => $rekapPenerimaanHeader->totalRows,
                'totalPages' => $rekapPenerimaanHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreRekapPenerimaanHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();


        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'tgltransaksi'  => date('Y-m-d', strtotime($request->tgltransaksi)),
                'bank_id' => $request->bank_id,

                "tgltransaksi_detail" => $request->tgltransaksi_detail,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,
                "nominal" => $request->nominal,
                "keterangan_detail" => $request->keterangan_detail,

            ];

            $rekapPenerimaanHeader = (new RekapPenerimaanHeader())->processStore($data);
            $rekapPenerimaanHeader->position = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable())->position;
            if ($request->limit == 0) {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / (10));
            } else {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));
            }
            $rekapPenerimaanHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $rekapPenerimaanHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $rekapPenerimaanHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(RekapPenerimaanHeader $rekapPenerimaanHeader, $id)
    {
        $data = $rekapPenerimaanHeader->findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $rekapPenerimaanHeader->getRekapPenerimaanHeader($id),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateRekapPenerimaanHeaderRequest $request, RekapPenerimaanHeader $rekappenerimaanheader)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'tgltransaksi'  => date('Y-m-d', strtotime($request->tgltransaksi)),
                'bank_id' => $request->bank_id,

                "tgltransaksi_detail" => $request->tgltransaksi_detail,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,
                "nominal" => $request->nominal,
                "keterangan_detail" => $request->keterangan_detail,

            ];

            $rekapPenerimaanHeader = (new RekapPenerimaanHeader())->processUpdate($rekappenerimaanheader, $data);
            $rekapPenerimaanHeader->position = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable())->position;
            if ($request->limit == 0) {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / (10));
            } else {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));
            }
            $rekapPenerimaanHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $rekapPenerimaanHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $rekapPenerimaanHeader
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
    public function destroy(DestroyRekapPenerimaanHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $rekapPenerimaanHeader = (new RekapPenerimaanHeader())->processDestroy($id, 'DELETE REKAP PENERIMAAN HEADER');
            $selected = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable(), true);
            $rekapPenerimaanHeader->position = $selected->position;
            $rekapPenerimaanHeader->id = $selected->id;
            if ($request->limit == 0) {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / (10));
            } else {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));
            }
            $rekapPenerimaanHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $rekapPenerimaanHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $rekapPenerimaanHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL DATA
     */
    public function approval(ApprovalRekapPenerimaanRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'rekapId' => $request->rekapId
            ];
            $rekapPenerimaanHeader = (new RekapPenerimaanHeader())->processApproval($data);

            DB::commit();
            return response([
                'message' => 'Berhasil',
                'data' => $rekapPenerimaanHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = RekapPenerimaanHeader::findOrFail($id);
        $nobukti = $pengeluaran->nobukti ?? '';
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup=$parameter->cekText('TUTUP BUKU','TUTUP BUKU') ?? '1900-01-01';
        $tgltutup=date('Y-m-d', strtotime($tgltutup));

        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $pengeluaran->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( '.date('d-m-Y', strtotime($tgltutup)).' )';
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
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

    public function getPenerimaan(Request $request)
    {
        $penerimaan = new PenerimaanHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $penerimaan->getRekapPenerimaanHeader($request->bank, date('Y-m-d', strtotime($request->tglbukti))),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $penerimaan->totalRows,
                'totalPages' => $penerimaan->totalPages,
                'totalNominal' => $penerimaan->totalNominal,
            ]
        ]);
    }

    public function getRekapPenerimaan($id)
    {
        $rekapPenerimaan = new RekapPenerimaanHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $rekapPenerimaan->getRekapPenerimaanHeader($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $rekapPenerimaan->totalRows,
                'totalPages' => $rekapPenerimaan->totalPages,
                'totalNominal' => $rekapPenerimaan->totalNominal,
            ]
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $rekapPenerimaan = RekapPenerimaanHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($rekapPenerimaan->statuscetak != $statusSudahCetak->id) {
                $rekapPenerimaan->statuscetak = $statusSudahCetak->id;
                $rekapPenerimaan->tglbukacetak = date('Y-m-d H:i:s');
                $rekapPenerimaan->userbukacetak = auth('api')->user()->name;
                $rekapPenerimaan->jumlahcetak = $rekapPenerimaan->jumlahcetak + 1;
                if ($rekapPenerimaan->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($rekapPenerimaan->getTable()),
                        'postingdari' => 'PRINT REKAP PENERIMAAN HEADER',
                        'idtrans' => $rekapPenerimaan->id,
                        'nobuktitrans' => $rekapPenerimaan->id,
                        'aksi' => 'PRINT',
                        'datajson' => $rekapPenerimaan->toArray(),
                        'modifiedby' => $rekapPenerimaan->modifiedby
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $rekapPenerimaan = new RekapPenerimaanHeader();
        return response([
            'data' => $rekapPenerimaan->getExport($id)
        ]);
    }
}
