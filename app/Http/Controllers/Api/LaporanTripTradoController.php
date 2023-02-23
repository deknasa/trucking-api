<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanTripTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanTripTradoController extends Controller
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

    /**
     * @ClassName
     */
    public function report(Request $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;

        $report = LaporanTripTrado::getReport($sampai, $dari);
        return response([
            'data' => $report
        ]);
    }
}
