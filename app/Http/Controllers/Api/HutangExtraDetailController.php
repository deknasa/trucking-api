<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\HutangExtraDetail;
use App\Http\Requests\StoreHutangExtraDetailRequest;
use App\Http\Requests\UpdateHutangExtraDetailRequest;
use App\Models\HutangDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HutangExtraDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index(): JsonResponse
    {
        $hutangExtraDetail = new HutangExtraDetail();

        return response()->json([
            'data' => $hutangExtraDetail->get(),
            'attributes' => [
                'totalRows' => $hutangExtraDetail->totalRows,
                'totalPages' => $hutangExtraDetail->totalPages,
                'totalNominal' => $hutangExtraDetail->totalNominal,
            ]
        ]);
    }

    public function hutang(): JsonResponse
    {
        $hutangDetail = new HutangDetail();

        return response()->json([
            'data' => $hutangDetail->getHutangFromHutangExtra(request()->nobukti_hutang),
            'attributes' => [
                'totalRows' => $hutangDetail->totalRows,
                'totalPages' => $hutangDetail->totalPages,
                'totalNominal' => $hutangDetail->totalNominal
            ]
        ]);
    }

    
}
