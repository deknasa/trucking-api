<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProsesUangJalanSupirDetail;
use App\Http\Requests\StoreProsesUangJalanSupirDetailRequest;
use App\Http\Requests\UpdateProsesUangJalanSupirDetailRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesUangJalanSupirDetailController extends Controller
{
    public function index(): JsonResponse
    {
        $prosesUangJalanSupir = new ProsesUangJalanSupirDetail();

        return response()->json([
            'data' => $prosesUangJalanSupir->get(),
            'attributes' => [
                'totalRows' => $prosesUangJalanSupir->totalRows,
                'totalPages' => $prosesUangJalanSupir->totalPages,
                'totalNominal' => $prosesUangJalanSupir->totalNominal
            ]
        ]);
    }


    public function store(StoreProsesUangJalanSupirDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            
            $prosesUangJalan = new ProsesUangJalanSupirDetail();

            $prosesUangJalan->prosesuangjalansupir_id = $request->prosesuangjalansupir_id;
            $prosesUangJalan->nobukti = $request->nobukti;
            $prosesUangJalan->penerimaantrucking_bank_id = $request->penerimaantrucking_bank_id ?? '';
            $prosesUangJalan->penerimaantrucking_tglbukti = $request->penerimaantrucking_tglbukti ?? '';
            $prosesUangJalan->penerimaantrucking_nobukti = $request->penerimaantrucking_nobukti ?? '';
            $prosesUangJalan->pengeluarantrucking_bank_id = $request->pengeluarantrucking_bank_id ?? '';
            $prosesUangJalan->pengeluarantrucking_tglbukti = $request->pengeluarantrucking_tglbukti ?? '';
            $prosesUangJalan->pengeluarantrucking_nobukti = $request->pengeluarantrucking_nobukti ?? '';
            $prosesUangJalan->pengembaliankasgantung_bank_id = $request->pengembaliankasgantung_bank_id ?? '';
            $prosesUangJalan->pengembaliankasgantung_tglbukti = $request->pengembaliankasgantung_tglbukti ?? '';
            $prosesUangJalan->pengembaliankasgantung_nobukti = $request->pengembaliankasgantung_nobukti ?? '';
            $prosesUangJalan->statusprosesuangjalan = $request->statusprosesuangjalan;
            $prosesUangJalan->nominal = $request->nominal;
            $prosesUangJalan->keterangan = $request->keterangan;
            $prosesUangJalan->modifiedby = auth('api')->user()->name;
            
            $prosesUangJalan->save();
            DB::commit();

            return [
                'error' => false,
                'detail' => $prosesUangJalan,
                'id' => $prosesUangJalan->id,
                'tabel' => $prosesUangJalan->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
