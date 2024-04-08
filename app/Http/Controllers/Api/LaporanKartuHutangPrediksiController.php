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

        $sampai = $request->sampai;
        $LaporanKartuHutangPrediksi = new LaporanKartuHutangPrediksi();

        $dataHutangPrediksi = $LaporanKartuHutangPrediksi->getReport($sampai,$dari);

        if (count($dataHutangPrediksi) == 0) {
            return response([
                'data' => $dataHutangPrediksi,
                'message' => 'tidak ada data'
            ], 500);
        }else{
            
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $dataHutangPrediksi,
                'message' => 'berhasil',
                'namacabang' => 'CABANG ' . $getCabang->namacabang
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
    
    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;

        $LaporanKartuHutangPrediksi = new LaporanKartuHutangPrediksi();

        $dataHutangPrediksi = $LaporanKartuHutangPrediksi->getReport($sampai,$dari);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
        ->select('cabang.namacabang')
        ->join("parameter", 'parameter.text', 'cabang.id')
        ->where('parameter.grp', 'ID CABANG')
        ->first();


        return response([
            'data' => $dataHutangPrediksi,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }
}
