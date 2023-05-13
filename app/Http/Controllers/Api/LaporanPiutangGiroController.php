<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanPiutangGiro;

class LaporanPiutangGiroController extends Controller
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

        $report = [
            [
                'judul' => 'PT. Transporindo Agung Sejahtera',
                'subjudul' => 'Laporan Piutang Giro',
                'nobukti' => 'BPGT M-BCA 0001/I/2023',
                'nowarkat' => '79007',
                'nominal' => '30.900.128,00',
                'tgljt' => '18 Jan 2023',
                'shipper' => 'TAS-EXP',
                'nobukticair' => 'BMT M-BCA 0009/I/2023',
                'tglcair' => '18 Jan 2023',
            ], 
            [
                'nobukti' => 'BPGT M-BCA 0001/I/2023',
                'nowarkat' => '70007',
                'nominal' => '67.000.980,00',
                'tgljt' => '28 Jan 2023',
                'shipper' => 'TAS-EXP',
                'nobukticair' => 'BMT M-BCA 0009/I/2023',
                'tglcair' => '28 Jan 2023',
            ], 
        ];
        return response([
            'data' => $report
        ]);
    }
}
