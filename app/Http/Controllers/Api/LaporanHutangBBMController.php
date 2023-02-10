<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanHutangBBM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanHutangBBMController extends Controller
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

        $report = LaporanHutangBBM::getReport($sampai);
        return response([
            'data' => $report
        ]);
    }
}
