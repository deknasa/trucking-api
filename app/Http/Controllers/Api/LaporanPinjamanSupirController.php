<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPinjamanSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPinjamanSupirController extends Controller
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

        $report = LaporanPinjamanSupir::getReport($sampai);
        return response([
            'data' => $report
        ]);
    }
}
