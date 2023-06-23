<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanJurnalUmum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanJurnalUmumController extends Controller
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
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $laporanjurnalumum = new LaporanJurnalUmum();


        $laporan_jurnalumum = $laporanjurnalumum->getReport($dari, $sampai, );

        foreach($laporan_jurnalumum as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            $item->dari = date('d-m-Y', strtotime(substr($item->dari, 0, 10)));
            $item->sampai = date('d-m-Y', strtotime(substr($item->sampai, 0, 10)));
        }
        return response([
            'data' => $laporan_jurnalumum
            // 'data' => $report
        ]);
    }

   /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $laporanjurnalumum = new LaporanJurnalUmum();

        // $report = LaporanKasGantung::getReport($sampai, $jenis);
        // $report = [
        //     [
        //         'tanggal' => "24/2/2023",
        //         "nobukti" => "KGT 0002/II/2023",
        //         "keterangian" => "BELANJAS",
        //         "debet" => "25412",
        //         "kredit" => "351251",
        //         "saldo" => "151511"
        //     ]
        // ];
        return response([
            'data' => $laporanjurnalumum->getReport($dari, $sampai)
            // 'data' => $report
        ]);
    }
}
