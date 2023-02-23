<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanSupirLebihDariTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanSupirLebihDariTradoController extends Controller
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

        $report = LaporanSupirLebihDariTrado::getReport($sampai, $dari);
        return response([
            'data' => $report
        ]);
    }
}
