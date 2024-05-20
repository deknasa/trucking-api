<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangDetail;
use App\Http\Requests\StorePelunasanPiutangDetailRequest;
use App\Http\Requests\UpdatePelunasanPiutangDetailRequest;
use App\Models\PelunasanPiutangHeader;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PelunasanPiutangDetailController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPIKAN DATA
     */

    public function index(): JsonResponse
    {
        $pelunasanPiutang = new PelunasanPiutangDetail();
        return response()->json([
            'data' => $pelunasanPiutang->get(),
            'attributes' => [
                'totalRows' => $pelunasanPiutang->totalRows,
                'totalPages' => $pelunasanPiutang->totalPages,
                'totalNominal' => $pelunasanPiutang->totalNominal,
                'totalPotongan' => $pelunasanPiutang->totalPotongan,
                'totalPotonganPPH' => $pelunasanPiutang->totalPotonganPPH,
                'totalNominalLebih' => $pelunasanPiutang->totalNominalLebih,
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function getPelunasan(): JsonResponse
    {
        
        $pelunasanPiutang = new PelunasanPiutangDetail();
        if (request()->nobukti != 'false' && request()->nobukti != null) {
            $fetch = PelunasanPiutangHeader::from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
            request()->pelunasanpiutang_id = $fetch->id;
            return response()->json([
                'data' => $pelunasanPiutang->get(request()->pelunasanpiutang_id),
                'attributes' => [
                    'totalRows' => $pelunasanPiutang->totalRows,
                    'totalPages' => $pelunasanPiutang->totalPages,
                    'totalNominal' => $pelunasanPiutang->totalNominal
                ]
            ]);
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $pelunasanPiutang->totalRows,
                    'totalPages' => $pelunasanPiutang->totalPages,
                    'totalNominal' => 0
                ]
            ]);
        }
    }

    public function store(StorePelunasanPiutangDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $pelunasanpiutangdetail = new PelunasanPiutangDetail();

            $pelunasanpiutangdetail->pelunasanpiutang_id = $request->pelunasanpiutang_id;
            $pelunasanpiutangdetail->nobukti = $request->nobukti;
            $pelunasanpiutangdetail->nominal = $request->nominal;
            $pelunasanpiutangdetail->piutang_nobukti = $request->piutang_nobukti;
            $pelunasanpiutangdetail->keterangan = $request->keterangan;
            $pelunasanpiutangdetail->potongan = $request->potongan;
            $pelunasanpiutangdetail->coapotongan = $request->coapotongan;
            $pelunasanpiutangdetail->invoice_nobukti = $request->invoice_nobukti;
            $pelunasanpiutangdetail->keteranganpotongan = $request->keteranganpotongan;
            $pelunasanpiutangdetail->nominallebihbayar = $request->nominallebihbayar;
            $pelunasanpiutangdetail->coalebihbayar = $request->coalebihbayar;

            $pelunasanpiutangdetail->modifiedby = auth('api')->user()->name;
            $pelunasanpiutangdetail->save();
            DB::commit();
            return [
                'error' => false,
                'detail' => $pelunasanpiutangdetail,
                'id' => $pelunasanpiutangdetail->id,
                'tabel' => $pelunasanpiutangdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
