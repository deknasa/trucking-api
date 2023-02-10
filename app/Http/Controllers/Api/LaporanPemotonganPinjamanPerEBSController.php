<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPemotonganPinjamanPerEBS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPemotonganPinjamanPerEBSController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => 0,
                'totalPages' => 0
            ]
        ]);
    }

    public function report(Request $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;

        $report = LaporanPemotonganPinjamanPerEBS::getReport($sampai, $dari);
        return response([
            'data' => $report
        ]);
    }
}
