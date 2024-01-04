<?php

namespace App\Http\Controllers\Api;

use App\Models\HutangHeader;
use App\Models\HutangDetail;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\StoreHutangDetailRequest;
use App\Http\Requests\UpdateHutangDetailRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Bank;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;



class HutangDetailController extends Controller
{

   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(): JsonResponse
    {
        $hutangDetail = new HutangDetail();

        return response()->json([
            'data' => $hutangDetail->get(),
            'attributes' => [
                'totalRows' => $hutangDetail->totalRows,
                'totalPages' => $hutangDetail->totalPages,
                'totalNominal' => $hutangDetail->totalNominal,
            ]
        ]);
    }

    public function history(): JsonResponse
    {
        $hutangDetail = new HutangDetail();

        return response()->json([
            'data' => $hutangDetail->getHistory(),
            'attributes' => [
                'totalRows' => $hutangDetail->totalRows,
                'totalPages' => $hutangDetail->totalPages,
                'totalNominal' => $hutangDetail->totalNominal,
                'totalPotongan' => $hutangDetail->totalPotongan
            ]
        ]);
    }


    public function store(StoreHutangDetailRequest $request)
    {
        // DB::beginTransaction();


        try {
            $hutangdetail = new HutangDetail();
            $hutangdetail->hutang_id = $request->hutang_id;
            $hutangdetail->nobukti = $request->nobukti;
            $hutangdetail->tgljatuhtempo = date('Y-m-d', strtotime($request->tgljatuhtempo));
            $hutangdetail->total = $request->total;
            $hutangdetail->cicilan = $request->cicilan;
            $hutangdetail->totalbayar = $request->totalbayar;
            $hutangdetail->keterangan = $request->keterangan;
            $hutangdetail->modifiedby = auth('api')->user()->name;

            $hutangdetail->save();

            // DB::commit();
            return [
                'error' => false,
                'detail' => $hutangdetail,
                'id' => $hutangdetail->id,
                'tabel' => $hutangdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            // DB::rollBack();
            throw $th;
        }
    }

    public function addrow(StoreHutangDetailRequest $request)
    {
        return true;
    }
}
