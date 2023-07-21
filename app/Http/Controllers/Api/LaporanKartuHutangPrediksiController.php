<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKartuHutangPrediksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKartuHutangPrediksiController extends Controller
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

        $sampai = $request->sampai;
        $LaporanKartuHutangPrediksi = new LaporanKartuHutangPrediksi();

        $dataHutangPrediksi = $LaporanKartuHutangPrediksi->getReport($sampai,$dari);

        if (count($dataHutangPrediksi) == 0) {
            return response([
                'data' => $dataHutangPrediksi,
                'message' => 'tidak ada data'
            ], 500);
        }else{
            return response([
                'data' => $dataHutangPrediksi,
                'message' => 'berhasil'
            ]);
        }





        // $report = LaporanKartuHutangPrediksi::getReport($sampai, $dari);
        // $report = [
        //     [
        //         "noebs" => 'BKT-M BCA 0003/II/2023',
        //         'tanggal' => '23/2/2023',
        //         'nobukti' => '',
        //         'keterangan' => 'TES KETERANGAN I',
        //         'nominal' => '123412',
        //         'bayar' => '0',
        //         'saldo' => '214124124'

        //     ]
        // ];
        // return response([
        //     'data' => $report
        // ]);
    }
}
