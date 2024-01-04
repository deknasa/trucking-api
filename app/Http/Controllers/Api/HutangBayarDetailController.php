<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\HutangBayarDetail;
use App\Http\Requests\StoreHutangBayarDetailRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class HutangBayarDetailController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(): JsonResponse
    {
        $hutangBayar = new HutangBayarDetail();

        return response()->json([
            'data' => $hutangBayar->get(),
            'attributes' => [
                'totalRows' => $hutangBayar->totalRows,
                'totalPages' => $hutangBayar->totalPages,
                'totalNominal' => $hutangBayar->totalNominal,
                'totalPotongan' => $hutangBayar->totalPotongan,
            ]
        ]);
    }


    public function store(StoreHutangBayarDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $hutangbayarDetail = new HutangBayarDetail();
            $hutangbayarDetail->hutangbayar_id = $request->hutangbayar_id;
            $hutangbayarDetail->nobukti = $request->nobukti;
            $hutangbayarDetail->nominal = $request->nominal;
            $hutangbayarDetail->hutang_nobukti = $request->hutang_nobukti;
            $hutangbayarDetail->cicilan = $request->cicilan;
            $hutangbayarDetail->potongan = $request->potongan;
            $hutangbayarDetail->keterangan = $request->keterangan;
            $hutangbayarDetail->modifiedby = auth('api')->user()->name;
            $hutangbayarDetail->save();

            DB::commit();
            return [
                'error' => false,
                'detail' => $hutangbayarDetail,
                'id' => $hutangbayarDetail->id,
                'tabel' => $hutangbayarDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
