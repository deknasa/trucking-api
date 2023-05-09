<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanKartuPiutangPerPelanggan;


class LaporanKartuPiutangPerPelangganController extends Controller
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
        $kategori = $request->kategori;

        $report = [
            [
                'kodepelanggan' => 'SAMUDERA',
                'namapelanggan' => 'SAMUDERA',
                'memoheader' => 'Saldo Awal',
                'nobukti' => 'Saldo Awal',
                'tgl' => '',
                'tgljt' => '',
                'cicilanke' => '0.00',
                'nominal' => '0.00',
                'terima' => '0.00',
                'saldo' =>  '0.00',
                'pelanggandari' => '{SEMUA}',
                'pelanggansampai' => '{SEMUA}'
            ], 
        ];
        return response([
            'data' => $report
        ]);
    }
}
