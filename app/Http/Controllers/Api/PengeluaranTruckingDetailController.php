<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranTruckingDetail;
use App\Http\Requests\StorePengeluaranTruckingDetailRequest;
use App\Http\Requests\UpdatePengeluaranTruckingDetailRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengeluaranTruckingDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(): JsonResponse
    {
        $pengeluaranTrucking = new PengeluaranTruckingDetail();

        return response()->json([
            'data' => $pengeluaranTrucking->get(),
            'attributes' => [
                'totalRows' => $pengeluaranTrucking->totalRows,
                'totalPages' => $pengeluaranTrucking->totalPages,
                'totalNominal' => $pengeluaranTrucking->totalNominal
            ]
        ]);
    }



    public function store(StorePengeluaranTruckingDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $pengeluarantruckingDetail = new PengeluaranTruckingDetail();

            $pengeluarantruckingDetail->pengeluarantruckingheader_id = $request->pengeluarantruckingheader_id;
            $pengeluarantruckingDetail->nobukti = $request->nobukti;
            $pengeluarantruckingDetail->supir_id = $request->supir_id;
            $pengeluarantruckingDetail->penerimaantruckingheader_nobukti = $request->penerimaantruckingheader_nobukti ?? "";
            $pengeluarantruckingDetail->stok_id = $request->stok_id ?? 0;
            $pengeluarantruckingDetail->pengeluaranstok_nobukti = $request->pengeluaranstok_nobukti ?? "";
            $pengeluarantruckingDetail->qty = $request->qty ?? 0;
            $pengeluarantruckingDetail->harga = $request->harga ?? 0;
            $pengeluarantruckingDetail->trado_id = $request->trado_id ?? 0;
            $pengeluarantruckingDetail->keterangan = $request->keterangan;
            $pengeluarantruckingDetail->invoice_nobukti = $request->invoice_nobukti;
            $pengeluarantruckingDetail->orderantrucking_nobukti = $request->orderantrucking_nobukti;
            $pengeluarantruckingDetail->nominal = $request->nominal;
            $pengeluarantruckingDetail->modifiedby = auth('api')->user()->name;

            $pengeluarantruckingDetail->save();
            DB::commit();
            return [
                'error' => false,
                'detail' => $pengeluarantruckingDetail,
                'id' => $pengeluarantruckingDetail->id,
                'tabel' => $pengeluarantruckingDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            // dd($th);
            DB::rollBack();
            throw $th;
        }
    }


}
