<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanTripGandenganDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanTripGandenganDetailController extends Controller
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
        $jenis = $request->jenis;

        $report = LaporanTripGandenganDetail::getReport($sampai, $jenis);
        return response([
            'data' => $report
        ]);
    }
}
