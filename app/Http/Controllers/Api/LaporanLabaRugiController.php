<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanPiutangGiro;

class LaporanLabaRugiController extends Controller
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

        $report = [
            [
                'judul' => 'PT. Transporindo Agung Sejahtera',
                'subjudul' => 'Laporan Laba Rugi Divici Trucking',
                'pndptusaha' => '414.623.710,00',
                'pndptusahamdn' => '414.623.710,00',
                'pndptlainheader' => '18.765.282,00',
                'pndptlain' => '18.746.236,00',
                'pndptbunga' => '19.046,50',
                'potonganpndpt' => '0,00',
                'penghapusanpndpt' => '0,00',

            ], 
        ];
        return response([
            'data' => $report
        ]);
    }
}
