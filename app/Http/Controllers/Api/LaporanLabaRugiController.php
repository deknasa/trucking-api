<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanLabaRugi;

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
        // $bulan = $request->bulan;
        // $tahun = $request->tahun;

        // $laporanlabarugi = new LaporanLabaRugi();


        // $laporan_labarugi= $laporanlabarugi->getReport($bulan, $tahun);


        // return response([
        //     'data' => $laporan_labarugi

        // ]);


        // if ($request->isCheck) {
        //     return response([
        //         'data' => 'ok'
        //     ]);
        // } else {

            $bulan = substr($request->sampai,0,2);
            $tahun = substr($request->sampai,3,4);

            $laporanlabarugi = new LaporanLabaRugi();


            $laporan_labarugi = $laporanlabarugi->getReport($bulan, $tahun);



            return response([
                'data' => $laporan_labarugi,
            ]);
        // }
    }

    /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $laporanlabarugi = new LaporanLabaRugi();


        $laporan_labarugi = $laporanlabarugi->getExport($bulan, $tahun);
        // foreach($laporan_labarugi as $item){
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }

        return response([
            'data' => $laporan_labarugi
            // 'data' => $report
        ]);
    }
}
