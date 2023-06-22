<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPelunasanPiutangHeaderRequest;
use App\Http\Requests\DestroyPenerimaanHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\PelunasanPiutangHeader;
use App\Models\PelunasanPiutangDetail;
use App\Models\PiutangHeader;


use App\Http\Requests\StorePelunasanPiutangHeaderRequest;
use App\Http\Requests\UpdatePelunasanPiutangHeaderRequest;
use App\Http\Requests\StorePelunasanPiutangDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreNotaDebetHeaderRequest;
use App\Http\Requests\StoreNotaKreditHeaderRequest;
use App\Http\Requests\StorePenerimaanGiroHeaderRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\UpdateNotaDebetDetailRequest;
use App\Http\Requests\UpdateNotaDebetHeaderRequest;
use App\Http\Requests\UpdateNotaKreditHeaderRequest;
use App\Http\Requests\UpdatePenerimaanGiroHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\LogTrail;
use App\Models\Agen;
use App\Models\AkunPusat;
use App\Models\AlatBayar;
use App\Models\Cabang;
use App\Models\Bank;
use App\Models\Error;
use App\Models\JurnalUmumHeader;
use App\Models\NotaDebetHeader;
use App\Models\NotaKreditHeader;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\PenerimaanDetail;
use App\Models\PenerimaanGiroHeader;
use App\Models\PenerimaanHeader;
use App\Models\SaldoPiutang;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class PelunasanPiutangHeaderController extends Controller
{
   /**
     * @ClassName 
     * PelunasanPiutangHeader
     * @Detail1 PelunasanPiutangDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $pengeluarantruckingheader = new PelunasanPiutangHeader();
        return response([
            'data' => $pengeluarantruckingheader->get(),
            'attributes' => [
                'totalRows' => $pengeluarantruckingheader->totalRows,
                'totalPages' => $pengeluarantruckingheader->totalPages
            ]
        ]);
    }

    public function default()
    {
        $pelunasan = new PelunasanPiutangHeader();
        return response([
            'status' => true,
            'data' => $pelunasan->default(),
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePelunasanPiutangHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'agen_id' => $request->agen_id,
                'agen' => $request->agen,
                'nowarkat' => $request->nowarkat,
                'piutang_id' => $request->piutang_id,
                'piutang_nobukti' => $request->piutang_nobukti,
                'nominallebihbayar' => $request->nominallebihbayar,
                'bayar' => $request->bayar,
                'keterangan' => $request->keterangan,
                'potongan' => $request->potongan,
                'coapotongan' => $request->coapotongan,
                'keteranganpotongan' => $request->keteranganpotongan,
                'nominallebihbayar' => $request->nominallebihbayar,
            ];
            $pelunasanPiutangHeader = (new PelunasanPiutangHeader())->processStore($data);
            $pelunasanPiutangHeader->position = $this->getPosition($pelunasanPiutangHeader, $pelunasanPiutangHeader->getTable())->position;
            $pelunasanPiutangHeader->page = ceil($pelunasanPiutangHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pelunasanPiutangHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        // $data = PelunasanPiutangHeader::with(
        //     'pelunasanpiutangdetail',
        // )->find($id);

        $data = PelunasanPiutangHeader::findAll($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePelunasanPiutangHeaderRequest $request, PelunasanPiutangHeader $pelunasanpiutangheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'agen_id' => $request->agen_id,
                'agen' => $request->agen,
                'nowarkat' => $request->nowarkat,
                'piutang_id' => $request->piutang_id,
                'piutang_nobukti' => $request->piutang_nobukti,
                'nominallebihbayar' => $request->nominallebihbayar,
                'bayar' => $request->bayar,
                'keterangan' => $request->keterangan,
                'potongan' => $request->potongan,
                'coapotongan' => $request->coapotongan,
                'keteranganpotongan' => $request->keteranganpotongan,
                'nominallebihbayar' => $request->nominallebihbayar,
            ];
            $pelunasanPiutangHeader = (new PelunasanPiutangHeader())->processUpdate($pelunasanpiutangheader, $data);
            $pelunasanPiutangHeader->position = $this->getPosition($pelunasanPiutangHeader, $pelunasanPiutangHeader->getTable())->position;
            $pelunasanPiutangHeader->page = ceil($pelunasanPiutangHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $pelunasanPiutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(DestroyPelunasanPiutangHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pelunasanPiutangHeader = (new PelunasanPiutangHeader())->processDestroy($id, 'DELETE PELUNASAN PIUTANG');
            $selected = $this->getPosition($pelunasanPiutangHeader, $pelunasanPiutangHeader->getTable(), true);
            $pelunasanPiutangHeader->position = $selected->position;
            $pelunasanPiutangHeader->id = $selected->id;
            $pelunasanPiutangHeader->page = ceil($pelunasanPiutangHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pelunasanPiutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
        $getDetail = PelunasanPiutangDetail::where('pelunasanpiutang_id', $id)->get();

        $request['postingdari'] = "DELETE PELUNASAN PIUTANG";
        $pelunasanpiutangheader = new PelunasanPiutangHeader();
        $pelunasanpiutangheader = $pelunasanpiutangheader->lockAndDestroy($id);

        $newRequestPenerimaan = new DestroyPenerimaanHeaderRequest();
        $newRequestPenerimaan->postingdari = "DELETE PELUNASAN PIUTANG HEADER";
        if ($pelunasanpiutangheader) {
            $logTrail = [
                'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                'postingdari' => 'DELETE PELUNASAN PIUTANG HEADER',
                'idtrans' => $pelunasanpiutangheader->id,
                'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pelunasanpiutangheader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PELUNASAN PIUTANG DETAIL

            $logTrailPiutangDetail = [
                'namatabel' => 'PELUNASANPIUTANGDETAIL',
                'postingdari' => 'DELETE PELUNASAN PIUTANG DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPiutangDetail = new StoreLogTrailRequest($logTrailPiutangDetail);
            app(LogTrailController::class)->store($validatedLogTrailPiutangDetail);

            if ($pelunasanpiutangheader->penerimaan_nobukti != '-') {
                $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->penerimaan_nobukti)->first();
                app(PenerimaanHeaderController::class)->destroy($newRequestPenerimaan, $getPenerimaan->id);
            }
            if ($pelunasanpiutangheader->penerimaangiro_nobukti != '-') {
                $getGiro = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->penerimaangiro_nobukti)->first();
                app(PenerimaanGiroHeaderController::class)->destroy($request, $getGiro->id);
            }

            if ($pelunasanpiutangheader->notakredit_nobukti != '-') {
                $getNotaKredit = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->notakredit_nobukti)->first();
                app(NotaKreditHeaderController::class)->destroy($request, $getNotaKredit->id);
            }

            if ($pelunasanpiutangheader->notadebet_nobukti != '-') {
                $getNotaDebet = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->notadebet_nobukti)->first();
                app(NotaDebetHeaderController::class)->destroy($request, $getNotaDebet->id);
            }

            DB::commit();

            $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable(), true);
            $pelunasanpiutangheader->position = $selected->position;
            $pelunasanpiutangheader->id = $selected->id;
            $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pelunasanpiutangheader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function getpiutang($id)
    {
        $piutang = new PiutangHeader();
        return response([
            'data' => $piutang->getPiutang($id),
            'id' => $id,
            'attributes' => [
                'totalRows' => $piutang->totalRows,
                'totalPages' => $piutang->totalPages
            ]
        ]);
    }


    public function getPelunasanPiutang($id, $agenId)
    {
        $pelunasanpiutang = new PelunasanPiutangHeader();
        return response([
            'data' => $pelunasanpiutang->getPelunasanPiutang($id, $agenId),
            'attributes' => [
                'totalRows' => $pelunasanpiutang->totalRows,
                'totalPages' => $pelunasanpiutang->totalPages
            ]
        ]);
    }

    public function getDeletePelunasanPiutang($id, $agenId)
    {
        $pelunasanpiutang = new PelunasanPiutangHeader();
        return response([
            'data' => $pelunasanpiutang->getDeletePelunasanPiutang($id, $agenId),
            'attributes' => [
                'totalRows' => $pelunasanpiutang->totalRows,
                'totalPages' => $pelunasanpiutang->totalPages
            ]
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pelunasanpiutangheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
