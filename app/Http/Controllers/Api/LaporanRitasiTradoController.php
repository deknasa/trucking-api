<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanRitasiTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanRitasiTradoController extends Controller
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
        $laporanritasi = new LaporanRitasiTrado();
        // $report = LaporanRitasiTrado::getExport($periode);
         $export = [
            [
                'nopol' => "BK 213121",
            ],
         
        ];
        return response([
            'data' => $laporanritasi->getExport($periode)
            // 'data' => $export
        ]);
    }
}
