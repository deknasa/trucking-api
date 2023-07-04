<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PelunasanHutangDetail;
use App\Http\Requests\StorePelunasanHutangDetailRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PelunasanHutangDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(): JsonResponse
    {
        $PelunasanHutang = new PelunasanHutangDetail();

        return response()->json([
            'data' => $PelunasanHutang->get(),
            'attributes' => [
                'totalRows' => $PelunasanHutang->totalRows,
                'totalPages' => $PelunasanHutang->totalPages,
                'totalNominal' => $PelunasanHutang->totalNominal,
                'totalPotongan' => $PelunasanHutang->totalPotongan,
            ]
        ]);
    }


    public function store(StorePelunasanHutangDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $PelunasanHutangDetail = new PelunasanHutangDetail();
            $PelunasanHutangDetail->PelunasanHutang_id = $request->PelunasanHutang_id;
            $PelunasanHutangDetail->nobukti = $request->nobukti;
            $PelunasanHutangDetail->nominal = $request->nominal;
            $PelunasanHutangDetail->hutang_nobukti = $request->hutang_nobukti;
            $PelunasanHutangDetail->cicilan = $request->cicilan;
            $PelunasanHutangDetail->potongan = $request->potongan;
            $PelunasanHutangDetail->keterangan = $request->keterangan;
            $PelunasanHutangDetail->modifiedby = auth('api')->user()->name;
            $PelunasanHutangDetail->save();

            DB::commit();
            return [
                'error' => false,
                'detail' => $PelunasanHutangDetail,
                'id' => $PelunasanHutangDetail->id,
                'tabel' => $PelunasanHutangDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
