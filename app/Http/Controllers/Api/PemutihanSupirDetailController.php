<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\pemutihansupirdetail;
use App\Http\Requests\StorePemutihanSupirDetailRequest;
use App\Http\Requests\UpdatePemutihanSupirDetailRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PemutihanSupirDetailController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(): JsonResponse
    {
        $pemutihanSupir = new pemutihansupirdetail();

        return response()->json([
            'data' => $pemutihanSupir->get(),
            'attributes' => [
                'totalRows' => $pemutihanSupir->totalRows,
                'totalPages' => $pemutihanSupir->totalPages,
                'totalNominal' => $pemutihanSupir->totalNominal
            ]
        ]);
    }

    public function store(StorepemutihansupirdetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $pemutihanDetail = new pemutihansupirdetail();

            $pemutihanDetail->pemutihansupir_id = $request->pemutihansupir_id;
            $pemutihanDetail->nobukti = $request->nobukti;
            $pemutihanDetail->pengeluarantrucking_nobukti = $request->pengeluarantrucking_nobukti;
            $pemutihanDetail->statusposting = $request->statusposting;
            $pemutihanDetail->nominal = $request->nominal;
            $pemutihanDetail->modifiedby = $request->modifiedby;

            $pemutihanDetail->save();
            DB::commit();

            return [
                'error' => false,
                'detail' => $pemutihanDetail,
                'id' => $pemutihanDetail->id,
                'tabel' => $pemutihanDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
