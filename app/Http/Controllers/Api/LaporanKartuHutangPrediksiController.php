<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKartuHutangPrediksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKartuHutangPrediksiController extends Controller
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

        $report = LaporanKartuHutangPrediksi::getReport($sampai, $dari);
        return response([
            'data' => $report
        ]);
    }
}
