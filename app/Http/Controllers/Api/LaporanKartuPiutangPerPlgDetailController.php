<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanKartuPiutangPerPlgDetail;

class LaporanKartuPiutangPerPlgDetailController extends Controller
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
        $sampai = $request->sampai;
        $dari = $request->dari;
        $pelanggandari_id = $request->pelanggandari_id;
        $pelanggansampai_id = $request->pelanggansampai_id;

        $report = [
            [
                'judul' => 'Transporindo Agung Sejahtera',
                'subjudul' => 'Laporan Kartu Piutang Per Pelanggan Detail',
                'kodepelanggan' => 'TAS ASP',
                'namapelanggan' => 'TAS ASP',
                'memoheader' => '(INV 0004/IV/2023)',
                'nobukti' => 'EPT 0001/IV/2023',
                'tgl' => '17-April-2023',
                'tgljt' => '17-April-2023',
                'cicilanke' => '1.00',
                'nominal' => '54.900.450,00',
                'terima' => '0.00',
                'saldo' =>  '54.900.450,00'
            ], 
            [
                'nobukti' => 'EPT 0001/IV/2023',
                'tgl' => '17-April-2023',
                'tgljt' => '17-April-2023',
                'cicilanke' => '1.00',
                'nominal' => '60.900.450,00',
                'terima' => '0.00',
                'saldo' =>  '54.900.450,00'
            ], 
            [
                'nobukti' => 'EPT 0001/IV/2023',
                'tgl' => '17-April-2023',
                'tgljt' => '17-April-2023',
                'cicilanke' => '1.00',
                'nominal' => '213.000,00',
                'terima' => '0.00',
                'saldo' =>  '54.900.450,00'
            ], 
            [
                'nobukti' => 'EPT 0001/IV/2023',
                'tgl' => '17-April-2023',
                'tgljt' => '17-April-2023',
                'cicilanke' => '1.00',
                'nominal' => '12.900.450,00',
                'terima' => '0.00',
                'saldo' =>  '54.900.450,00'
            ], 
        ];
        return response([
            'data' => $report
        ]);
    }
}
