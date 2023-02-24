<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanRitasiGandengan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanRitasiGandenganController extends Controller
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
    public function export(Request $request)
    {
        $periode = $request->periode;

        $report = LaporanRitasiGandengan::getExport($periode);
        return response([
            'data' => $report
        ]);
    }
}
