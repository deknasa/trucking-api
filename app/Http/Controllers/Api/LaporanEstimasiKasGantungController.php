<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanEstimasiKasGantung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanEstimasiKasGantungController extends Controller
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

        $report = LaporanEstimasiKasGantung::getReport($sampai, $jenis);
        return response([
            'data' => $report
        ]);
    }
}
