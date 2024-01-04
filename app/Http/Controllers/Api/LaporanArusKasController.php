<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanArusKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanArusKasController extends Controller
{
     /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $periode = $request->periode;
        $laporanaruskas = new LaporanArusKas();
        
        // $report = LaporanKasGantung::getReport($sampai, $jenis);
        $report = [
            [
                'header' => 'Laporan Arus Kas',
                'keterangan' => 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Atque, aliquam?',
                'nilai' => '900000'
            ]
        ];
        return response([
            // 'data' => $laporankasgantung->getReport($periode)
            'data' => $report
        ]);
    }
}
