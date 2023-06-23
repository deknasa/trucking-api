<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PencairanGiroPengeluaranDetail;
use App\Http\Requests\StorePencairanGiroPengeluaranDetailRequest;
use App\Http\Requests\UpdatePencairanGiroPengeluaranDetailRequest;
use App\Models\PengeluaranDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PencairanGiroPengeluaranDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(): JsonResponse
    {
        $pencairanGiroPengeluaran = new PencairanGiroPengeluaranDetail();

        return response()->json([
            'data' => $pencairanGiroPengeluaran->get(),
            'attributes' => [
                'totalRows' => $pencairanGiroPengeluaran->totalRows,
                'totalPages' => $pencairanGiroPengeluaran->totalPages,
                'totalNominal' => $pencairanGiroPengeluaran->totalNominal
            ]
        ]);
    }


    public function store(StorePencairanGiroPengeluaranDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $pencairanGiroDetail = new PencairanGiroPengeluaranDetail();

            $pencairanGiroDetail->pencairangiropengeluaran_id = $request->pencairangiropengeluaran_id;
            $pencairanGiroDetail->nobukti = $request->nobukti;
            $pencairanGiroDetail->alatbayar_id = $request->alatbayar_id;
            $pencairanGiroDetail->nowarkat = $request->nowarkat;
            $pencairanGiroDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $pencairanGiroDetail->nominal = $request->nominal;
            $pencairanGiroDetail->coadebet = $request->coadebet;
            $pencairanGiroDetail->coakredit = $request->coakredit;
            $pencairanGiroDetail->keterangan = $request->keterangan;
            $pencairanGiroDetail->bulanbeban = $request->bulanbeban;
            $pencairanGiroDetail->modifiedby = auth('api')->user()->name;
            $pencairanGiroDetail->save();

            DB::commit();

            return [
                'error' => false,
                'detail' => $pencairanGiroDetail,
                'id' => $pencairanGiroDetail->id,
                'tabel' => $pencairanGiroDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
