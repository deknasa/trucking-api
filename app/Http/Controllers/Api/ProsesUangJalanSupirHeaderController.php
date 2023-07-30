<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPenerimaanTruckingHeaderRequest;
use App\Http\Requests\DestroyPengeluaranTruckingHeaderRequest;
use App\Http\Requests\DestroyPengembalianKasGantungHeaderRequest;
use App\Http\Requests\DestroyProsesUangJalanSupirHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\StoreProsesUangJalanSupirDetailRequest;
use App\Models\ProsesUangJalanSupirHeader;
use App\Http\Requests\StoreProsesUangJalanSupirHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdateProsesUangJalanSupirHeaderRequest;
use App\Models\AbsensiSupirHeader;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\Error;
use App\Models\Parameter;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranTrucking;
use App\Models\PengeluaranTruckingDetail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PengembalianKasGantungHeader;
use App\Models\ProsesUangJalanSupirDetail;
use App\Models\Supir;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesUangJalanSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * ProsesUangJalanSupirHeader
     * @Detail1 ProsesUangJalanSupirDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();
        return response([
            'data' => $prosesUangJalanSupir->get(),
            'attributes' => [
                'totalRows' => $prosesUangJalanSupir->totalRows,
                'totalPages' => $prosesUangJalanSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreProsesUangJalanSupirHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'absensisupir' => $request->absensisupir,
                'supir_id' => $request->supir_id,
                'supir' => $request->supir,
                'trado_id' => $request->trado_id,
                'tgltransfer' => $request->tgltransfer,
                'keterangantransfer' => $request->keterangantransfer,
                'nilaitransfer' => $request->nilaitransfer,
                'bank_idtransfer' => $request->bank_idtransfer,
                'banktransfer' => $request->banktransfer,
                'nobukti_kasbank' => $request->nobukti_kasbank,
                'tgladjust' => $request->tgladjust,
                'nilaiadjust' => $request->nilaiadjust,
                'keteranganadjust' => $request->keteranganadjust,
                'bank_idadjust' => $request->bank_idadjust,
                'bankadjust' => $request->bankadjust,
                'penerimaan_nobukti' => $request->penerimaan_nobukti,
                'nobuktideposit' => $request->nobuktideposit,
                'tgldeposit' => $request->tgldeposit,
                'nilaideposit' => $request->nilaideposit,
                'keterangandeposit' => $request->keterangandeposit,
                'bank_iddeposit' => $request->bank_iddeposit,
                'bankdeposit' => $request->bankdeposit,
                'penerimaandeposit_nobukti' => $request->penerimaandeposit_nobukti,
                'bank_idpengembalian' => $request->bank_idpengembalian,
                'nobuktipengeluaran' => $request->nobuktipengeluaran,
                'pjt_id' => $request->pjt_id,
                'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti,
                'keteranganpinjaman' => $request->keteranganpinjaman,
                'sisa' => $request->sisa,
                'nombayar' => $request->nombayar
            ];

            $prosesUangJalanSupir = (new ProsesUangJalanSupirHeader())->processStore($data);
            $prosesUangJalanSupir->position = $this->getPosition($prosesUangJalanSupir, $prosesUangJalanSupir->getTable())->position;
            if ($request->limit == 0) {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / (10));
            } else {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $prosesUangJalanSupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {
        $data = ProsesUangJalanSupirHeader::findAll($id);
        $detail = new ProsesUangJalanSupirDetail();

        return response([
            'status' => true,
            'data' => $data,
            'detail' => [
                'transfer' => $detail->findTransfer($id),
                'adjust' => $detail->adjustTransfer($id),
                'deposito' => $detail->deposito($id),
                'pengembalian' => $detail->pengembalian($id),
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdateProsesUangJalanSupirHeaderRequest $request, ProsesUangJalanSupirHeader $prosesuangjalansupirheader): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'absensisupir' => $request->absensisupir,
                'supir_id' => $request->supir_id,
                'supir' => $request->supir,
                'trado_id' => $request->trado_id,
                'tgltransfer' => $request->tgltransfer,
                'keterangantransfer' => $request->keterangantransfer,
                'nilaitransfer' => $request->nilaitransfer,
                'bank_idtransfer' => $request->bank_idtransfer,
                'banktransfer' => $request->banktransfer,
                'nobukti_kasbank' => $request->nobukti_kasbank,
                'tgladjust' => $request->tgladjust,
                'nilaiadjust' => $request->nilaiadjust,
                'keteranganadjust' => $request->keteranganadjust,
                'bank_idadjust' => $request->bank_idadjust,
                'bankadjust' => $request->bankadjust,
                'penerimaan_nobukti' => $request->penerimaan_nobukti,
                'nobuktideposit' => $request->nobuktideposit,
                'tgldeposit' => $request->tgldeposit,
                'nilaideposit' => $request->nilaideposit,
                'keterangandeposit' => $request->keterangandeposit,
                'bank_iddeposit' => $request->bank_iddeposit,
                'bankdeposit' => $request->bankdeposit,
                'penerimaandeposit_nobukti' => $request->penerimaandeposit_nobukti,
                'bank_idpengembalian' => $request->bank_idpengembalian,
                'nobuktipengeluaran' => $request->nobuktipengeluaran,
                'pjt_id' => $request->pjt_id,
                'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti,
                'keteranganpinjaman' => $request->keteranganpinjaman,
                'sisa' => $request->sisa,
                'nombayar' => $request->nombayar
            ];

            $prosesUangJalanSupir = (new ProsesUangJalanSupirHeader())->processUpdate($prosesuangjalansupirheader, $data);
            $prosesUangJalanSupir->position = $this->getPosition($prosesUangJalanSupir, $prosesUangJalanSupir->getTable())->position;
            if ($request->limit == 0) {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / (10));
            } else {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $prosesUangJalanSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(DestroyProsesUangJalanSupirHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $prosesUangJalanSupir = (new ProsesUangJalanSupirHeader())->processDestroy($id, 'DELETE PROSES UANG JALAN SUPIR');
            $selected = $this->getPosition($prosesUangJalanSupir, $prosesUangJalanSupir->getTable(), true);
            $prosesUangJalanSupir->position = $selected->position;
            $prosesUangJalanSupir->id = $selected->id;
            if ($request->limit == 0) {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / (10));
            } else {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $prosesUangJalanSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getPinjaman($supirId)
    {
        $prosesUangJalan = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalan->getPinjaman($supirId)
        ]);
    }
    public function getPengembalian($id)
    {
        $prosesUangJalan = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalan->getPengembalian($id)
        ]);
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = ProsesUangJalanSupirHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();


        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error as (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
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

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $prosesUangJalanSupir = ProsesUangJalanSupirHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($prosesUangJalanSupir->statuscetak != $statusSudahCetak->id) {
                $prosesUangJalanSupir->statuscetak = $statusSudahCetak->id;
                $prosesUangJalanSupir->tglbukacetak = date('Y-m-d H:i:s');
                $prosesUangJalanSupir->userbukacetak = auth('api')->user()->name;
                $prosesUangJalanSupir->jumlahcetak = $prosesUangJalanSupir->jumlahcetak + 1;
                if ($prosesUangJalanSupir->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($prosesUangJalanSupir->getTable()),
                        'postingdari' => 'PRINT PROSES UANG JALAN SUPIR HEADER',
                        'idtrans' => $prosesUangJalanSupir->id,
                        'nobuktitrans' => $prosesUangJalanSupir->id,
                        'aksi' => 'PRINT',
                        'datajson' => $prosesUangJalanSupir->toArray(),
                        'modifiedby' => $prosesUangJalanSupir->modifiedby
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
    {
    }

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();
        return response([
            'data' => $prosesUangJalanSupir->getExport($id)
        ]);
    }
}
