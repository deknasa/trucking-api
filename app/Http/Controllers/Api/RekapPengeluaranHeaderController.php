<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRekapPenerimaanRequest;
use App\Models\RekapPengeluaranHeader;
use App\Http\Requests\StoreRekapPengeluaranHeaderRequest;
use App\Http\Requests\UpdateRekapPengeluaranHeaderRequest;
use App\Http\Requests\DestroyRekapPengeluaranHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;

use App\Models\RekapPengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\Error;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreRekapPengeluaranDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use Illuminate\Http\JsonResponse;

class RekapPengeluaranHeaderController extends Controller
{
    /**
     * @ClassName 
     * RekapPengeluaranHeader
     * @Detail RekapPengeluaranDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $rekapPengeluaranHeader = new RekapPengeluaranHeader();
        return response([
            'data' => $rekapPengeluaranHeader->get(),
            'attributes' => [
                'totalRows' => $rekapPengeluaranHeader->totalRows,
                'totalPages' => $rekapPengeluaranHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreRekapPengeluaranHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'tgltransaksi' => $request->tgltransaksi,
                'bank_id' => $request->bank_id,
                'pengeluaran_nobukti' => $request->pengeluaran_nobukti,
                'tgltransaksi_detail' => $request->tgltransaksi_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal' => $request->nominal
            ];
            $rekapPengeluaranHeader = (new RekapPengeluaranHeader())->processStore($data);
            $rekapPengeluaranHeader->position = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable())->position;
            if ($request->limit == 0) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / (10));
            } else {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));
            }
            $rekapPengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $rekapPengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $rekapPengeluaranHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function show(RekapPengeluaranHeader $rekapPengeluaranHeader, $id)
    {
        $data = $rekapPengeluaranHeader->findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $rekapPengeluaranHeader->getRekapPengeluaranHeader($id)
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateRekapPengeluaranHeaderRequest $request, RekapPengeluaranHeader $rekappengeluaranheader)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'tgltransaksi' => $request->tgltransaksi,
                'bank_id' => $request->bank_id,
                'pengeluaran_nobukti' => $request->pengeluaran_nobukti,
                'tgltransaksi_detail' => $request->tgltransaksi_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal' => $request->nominal
            ];

            $rekapPengeluaranHeader = (new RekapPengeluaranHeader())->processUpdate($rekappengeluaranheader, $data);
            $rekapPengeluaranHeader->position = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable())->position;
            if ($request->limit == 0) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / (10));
            } else {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));
            }
            $rekapPengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $rekapPengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $rekapPengeluaranHeader
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
    public function destroy(DestroyRekapPengeluaranHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $rekapPengeluaranHeader = (new RekapPengeluaranHeader())->processDestroy($id, 'DELETE REKAP PENGELUARAN HEADER');
            $selected = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable(), true);
            $rekapPengeluaranHeader->position = $selected->position;
            $rekapPengeluaranHeader->id = $selected->id;
            if ($request->limit == 0) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / (10));
            } else {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));
            }
            $rekapPengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $rekapPengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $rekapPengeluaranHeader
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
            $rekapPengeluaranHeader = (new RekapPengeluaranHeader())->processApproval($data);

            DB::commit();

            return response([
                'message' => 'Berhasil',
                'data' => $rekapPengeluaranHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = RekapPengeluaranHeader::findOrFail($id);
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
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( '.date('d-m-Y', strtotime($tgltutup)).' ) <br> '.$keterangantambahanerror;
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

    public function getPengeluaran(Request $request)
    {
        $pengeluaran = new PengeluaranHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $pengeluaran->getRekapPengeluaranHeader($request->bank, date('Y-m-d', strtotime($request->tglbukti))),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pengeluaran->totalRows,
                'totalPages' => $pengeluaran->totalPages,
                'totalNominal' => $pengeluaran->totalNominal,
            ]
        ]);
    }

    public function getRekapPengeluaran($id)
    {
        $rekapPengeluaran = new RekapPengeluaranHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $rekapPengeluaran->getRekapPengeluaranHeader($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $rekapPengeluaran->totalRows,
                'totalPages' => $rekapPengeluaran->totalPages,
                'totalNominal' => $rekapPengeluaran->totalNominal,
            ]
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $rekapPengeluaran = RekapPengeluaranHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($rekapPengeluaran->statuscetak != $statusSudahCetak->id) {
                $rekapPengeluaran->statuscetak = $statusSudahCetak->id;
                $rekapPengeluaran->tglbukacetak = date('Y-m-d H:i:s');
                $rekapPengeluaran->userbukacetak = auth('api')->user()->name;
                $rekapPengeluaran->jumlahcetak = $rekapPengeluaran->jumlahcetak + 1;
                if ($rekapPengeluaran->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($rekapPengeluaran->getTable()),
                        'postingdari' => 'PRINT REKAP PENGELUARAN HEADER',
                        'idtrans' => $rekapPengeluaran->id,
                        'nobuktitrans' => $rekapPengeluaran->id,
                        'aksi' => 'PRINT',
                        'datajson' => $rekapPengeluaran->toArray(),
                        'modifiedby' => $rekapPengeluaran->modifiedby
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
        $rekapPengeluaran = new RekapPengeluaranHeader();
        return response([
            'data' => $rekapPengeluaran->getExport($id)
        ]);
    }
}
