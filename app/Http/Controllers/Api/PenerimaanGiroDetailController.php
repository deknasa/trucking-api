<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanGiroDetail;
use App\Http\Requests\StorePenerimaanGiroDetailRequest;
use App\Http\Requests\UpdatePenerimaanGiroDetailRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanGiroDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(): JsonResponse
    {
        $penerimaanGiro = new PenerimaanGiroDetail();

        return response()->json([
            'data' => $penerimaanGiro->get(),
            'attributes' => [
                'totalRows' => $penerimaanGiro->totalRows,
                'totalPages' => $penerimaanGiro->totalPages,
                'totalNominal' => $penerimaanGiro->totalNominal
            ]
        ]);
    }

    public function getDetail(){
        $penerimaanGiro = new PenerimaanGiroDetail();

        return response()->json([
            'data' => $penerimaanGiro->getDetailForPenerimaan(),
        ]);
    }

    public function store(StorePenerimaanGiroDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $penerimaangiroDetail = new PenerimaanGiroDetail();

            $penerimaangiroDetail->penerimaangiro_id = $request->penerimaangiro_id;
            $penerimaangiroDetail->nobukti = $request->nobukti;
            $penerimaangiroDetail->nowarkat = $request->nowarkat;
            $penerimaangiroDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $penerimaangiroDetail->nominal = $request->nominal;
            $penerimaangiroDetail->coadebet = $request->coadebet;
            $penerimaangiroDetail->coakredit = $request->coakredit;
            $penerimaangiroDetail->keterangan = $request->keterangan;
            $penerimaangiroDetail->bank_id = $request->bank_id;
            $penerimaangiroDetail->invoice_nobukti = $request->invoice_nobukti;
            $penerimaangiroDetail->bankpelanggan_id = $request->bankpelanggan_id;
            $penerimaangiroDetail->jenisbiaya = $request->jenisbiaya;
            $penerimaangiroDetail->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti;
            $penerimaangiroDetail->bulanbeban = $request->bulanbeban;
            $penerimaangiroDetail->modifiedby = auth('api')->user()->name;

            $penerimaangiroDetail->save();

            DB::commit();
            return [
                'error' => false,
                'detail' => $penerimaangiroDetail,
                'id' => $penerimaangiroDetail->id,
                'tabel' => $penerimaangiroDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
