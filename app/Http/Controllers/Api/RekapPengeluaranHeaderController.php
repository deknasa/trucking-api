<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

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
     * @Detail1 RekapPengeluaranDetailController
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
            if ($request->limit==0) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / (10));
            } else {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));
            }
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
        $data = $rekapPengeluaranHeader->find($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $rekapPengeluaranHeader->getRekapPengeluaranHeader($id)
        ]);
    }
    /**
     * @ClassName 
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
            if ($request->limit==0) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / (10));
            } else {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));
            }
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
     */
    public function destroy(DestroyRekapPengeluaranHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $rekapPengeluaranHeader = (new RekapPengeluaranHeader())->processDestroy($id, 'DELETE REKAP PENGELUARAN HEADER');
            $selected = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable(), true);
            $rekapPengeluaranHeader->position = $selected->position;
            $rekapPengeluaranHeader->id = $selected->id;
            if ($request->limit==0) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / (10));
            } else {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));
            }
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
     */
    public function approval($id)
    {
        DB::beginTransaction();
        $rekapPengeluaranHeader = RekapPengeluaranHeader::lockForUpdate()->findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($rekapPengeluaranHeader->statusapproval == $statusApproval->id) {
                $rekapPengeluaranHeader->statusapproval = $statusNonApproval->id;
            } else {
                $rekapPengeluaranHeader->statusapproval = $statusApproval->id;
            }

            $rekapPengeluaranHeader->tglapproval = date('Y-m-d', time());
            $rekapPengeluaranHeader->userapproval = auth('api')->user()->name;

            if ($rekapPengeluaranHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($rekapPengeluaranHeader->getTable()),
                    'postingdari' => 'UN/APPROVE REKAP PENGELUARAN HEADER',
                    'idtrans' => $rekapPengeluaranHeader->id,
                    'nobuktitrans' => $rekapPengeluaranHeader->nobukti,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $rekapPengeluaranHeader->toArray(),
                    'modifiedby' => $rekapPengeluaranHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $rekapPengeluaranHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = RekapPengeluaranHeader::findOrFail($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SAP'")
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
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
        } else {

            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
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
     */
    public function report()
    {}

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $rekapPengeluaran = new RekapPengeluaranHeader();
        return response([
            'data' => $rekapPengeluaran->getExport($id)
        ]);
    }
}
