<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;

use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumDetailRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JurnalUmumDetailController extends Controller
{

    public function index(): JsonResponse
    {
        $jurnalumumDetail = new JurnalUmumDetail();

        return response()->json([
            'data' => $jurnalumumDetail->get(),
            'attributes' => [
                'totalRows' => $jurnalumumDetail->totalRows,
                'totalPages' => $jurnalumumDetail->totalPages,
                'totalNominal' => $jurnalumumDetail->totalNominal
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StoreJurnalUmumDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $jurnalumumDetail = new JurnalUmumDetail();

            $jurnalumumDetail->jurnalumum_id = $request->jurnalumum_id;
            $jurnalumumDetail->nobukti = $request->nobukti;
            $jurnalumumDetail->tglbukti = $request->tglbukti;
            $jurnalumumDetail->coa = $request->coa;
            $jurnalumumDetail->nominal = $request->nominal;
            $jurnalumumDetail->keterangan = $request->keterangan ?? '';
            $jurnalumumDetail->modifiedby = auth('api')->user()->name;
            $jurnalumumDetail->baris = $request->baris;
            $jurnalumumDetail->save();

            DB::commit();

            return [
                'error' => false,
                'detail' => $jurnalumumDetail,
                'id' => $jurnalumumDetail->id,
                'tabel' => $jurnalumumDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
