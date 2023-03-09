<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmumPusatDetail;
use App\Http\Requests\StoreJurnalUmumPusatDetailRequest;
use App\Http\Requests\UpdateJurnalUmumPusatDetailRequest;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumPusatHeader;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalUmumPusatDetailController extends Controller
{
    public function index(): JsonResponse
    {
        $jurnalUmumPusatDetail = new JurnalUmumPusatDetail();

        return response()->json([
            'data' => $jurnalUmumPusatDetail->get(),
            'attributes' => [
                'totalRows' => $jurnalUmumPusatDetail->totalRows,
                'totalPages' => $jurnalUmumPusatDetail->totalPages,
                'totalNominalDebet' => $jurnalUmumPusatDetail->totalNominalDebet,
                'totalNominalKredit' => $jurnalUmumPusatDetail->totalNominalKredit
            ]
        ]);
    }


    public function store(StoreJurnalUmumPusatDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $jurnalUmumPusatDetail = new JurnalUmumPusatDetail();

            $jurnalUmumPusatDetail->jurnalumumpusat_id = $request->jurnalumumpusat_id;
            $jurnalUmumPusatDetail->nobukti = $request->nobukti;
            $jurnalUmumPusatDetail->tglbukti = $request->tglbukti;
            $jurnalUmumPusatDetail->coa = $request->coa;
            $jurnalUmumPusatDetail->nominal = $request->nominal;
            $jurnalUmumPusatDetail->keterangan = $request->keterangan;
            $jurnalUmumPusatDetail->modifiedby = auth('api')->user()->name;
            $jurnalUmumPusatDetail->baris = $request->baris;
            $jurnalUmumPusatDetail->save();

            DB::commit();

            return [
                'error' => false,
                'detail' => $jurnalUmumPusatDetail,
                'id' => $jurnalUmumPusatDetail->id,
                'tabel' => $jurnalUmumPusatDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
