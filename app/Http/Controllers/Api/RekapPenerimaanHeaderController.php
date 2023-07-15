<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
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
     * @Detail1 RekapPenerimaanDetailController
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
            $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));

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
        $data = $rekapPenerimaanHeader->find($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $rekapPenerimaanHeader->getRekapPenerimaanHeader($id),
        ]);
    }
    /**
     * @ClassName 
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
            $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));

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
     */
    public function destroy(DestroyRekapPenerimaanHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $rekapPenerimaanHeader = (new RekapPenerimaanHeader())->processDestroy($id, 'DELETE REKAP PENERIMAAN HEADER');
            $selected = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable(), true);
            $rekapPenerimaanHeader->position = $selected->position;
            $rekapPenerimaanHeader->id = $selected->id;
            $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));

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
     */
    public function approval($id)
    {
        DB::beginTransaction();
        $rekapPenerimaanHeader = RekapPenerimaanHeader::lockForUpdate()->findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($rekapPenerimaanHeader->statusapproval == $statusApproval->id) {
                $rekapPenerimaanHeader->statusapproval = $statusNonApproval->id;
            } else {
                $rekapPenerimaanHeader->statusapproval = $statusApproval->id;
            }

            $rekapPenerimaanHeader->tglapproval = date('Y-m-d', time());
            $rekapPenerimaanHeader->userapproval = auth('api')->user()->name;

            if ($rekapPenerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($rekapPenerimaanHeader->getTable()),
                    'postingdari' => 'UN/APPROVE REKAP PENERIMAAN HEADER',
                    'idtrans' => $rekapPenerimaanHeader->id,
                    'nobuktitrans' => $rekapPenerimaanHeader->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $rekapPenerimaanHeader->toArray(),
                    'modifiedby' => $rekapPenerimaanHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $rekapPenerimaanHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = RekapPenerimaanHeader::findOrFail($id);
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
     */
    public function report()
    {}

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $rekapPenerimaan = new RekapPenerimaanHeader();
        return response([
            'data' => $rekapPenerimaan->getExport($id)
        ]);
    }
}
