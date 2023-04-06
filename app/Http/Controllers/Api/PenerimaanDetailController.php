<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanDetail;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Models\PenerimaanHeader;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenerimaanDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        $penerimaanDetail = new PenerimaanDetail();


        return response([
            'data' => $penerimaanDetail->get(),
            'attributes' => [
                'totalRows' => $penerimaanDetail->totalRows,
                'totalPages' => $penerimaanDetail->totalPages,
                'totalNominal' => $penerimaanDetail->totalNominal,
            ]
        ]);
    }

    public function getPenerimaan(): JsonResponse
    {
        $penerimaanDetail = new PenerimaanDetail();
        if(request()->nobukti != 'false'){
            $fetch = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
            request()->penerimaan_id = $fetch->id;
            return response()->json([
                'data' => $penerimaanDetail->get(request()->penerimaan_id),
                'attributes' => [
                    'totalRows' => $penerimaanDetail->totalRows,
                    'totalPages' => $penerimaanDetail->totalPages,
                    'totalNominal' => $penerimaanDetail->totalNominal
                ]
            ]);
        }else{
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $penerimaanDetail->totalRows,
                    'totalPages' => $penerimaanDetail->totalPages,
                    'totalNominal' => 0
                ]
            ]);
        }
    }


    public function store(StorePenerimaanDetailRequest $request)
    {
        DB::beginTransaction();

        try {

            $penerimaanDetail = new PenerimaanDetail();

            $penerimaanDetail->penerimaan_id = $request->penerimaan_id;
            $penerimaanDetail->nobukti = $request->nobukti;
            $penerimaanDetail->nowarkat = $request->nowarkat ?? '';
            $penerimaanDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $penerimaanDetail->nominal = $request->nominal;
            $penerimaanDetail->coadebet = $request->coadebet;
            $penerimaanDetail->coakredit = $request->coakredit;
            $penerimaanDetail->keterangan = $request->keterangan;
            $penerimaanDetail->bank_id = $request->bank_id;
            $penerimaanDetail->invoice_nobukti = $request->invoice_nobukti ?? '';
            $penerimaanDetail->bankpelanggan_id = $request->bankpelanggan_id ?? 0;
            $penerimaanDetail->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti ?? '';
            $penerimaanDetail->bulanbeban = $request->bulanbeban;
            $penerimaanDetail->modifiedby = auth('api')->user()->name;

            $penerimaanDetail->save();

            DB::commit();

            return [
                'error' => false,
                'detail' => $penerimaanDetail,
                'id' => $penerimaanDetail->id,
                'tabel' => $penerimaanDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
