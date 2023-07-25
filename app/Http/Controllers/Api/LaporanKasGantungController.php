<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKasGantung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKasGantungController extends Controller
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
        $periode = date('Y-m-d', strtotime($request->periode)) ;
        
        $laporankasgantung = new LaporanKasGantung();
        
        // $report = LaporanKasGantung::getReport($sampai, $jenis);
        // $report = [
        //     [
        //         'tanggal' => "24/2/2023",
        //         "nobukti" => "KGT 0002/II/2023",
        //         "keterangan" => "BELANJAS",
        //         "debet" => "25412",
        //         "kredit" => "351251",
        //         "saldo" => "151511"
        //     ]
        // ];
        return response([
            'data' => $laporankasgantung->getReport($periode)
            // 'data' => $report
        ]);
    }

    /**
     * @ClassName
     */
    public function export(Request $request){
        $periode = date('Y-m-d', strtotime($request->periode)) ;
        $laporankasgantung = new LaporanKasGantung();
        //   $export = LaporanKasGantung::getExport($sampai, $jenis);
        // $export = [
        //     [
        //         'tanggal' => "24/2/2023",
        //         "nobukti" => "KGT 0002/II/2023",
        //         "keterangan" => "BELANJAS",
        //         "debet" => "25412",
        //         "kredit" => "351251",
        //         "saldo" => "151511"
        //     ],
        //     [
        //         'tanggal' => "24/2/2023",
        //         "nobukti" => "KGT 0002/II/2023",
        //         "keterangan" => "BELANJAS",
        //         "debet" => "25412",
        //         "kredit" => "351251",
        //         "saldo" => "151511"
        //     ],
        //     [
        //         'tanggal' => "24/2/2023",
        //         "nobukti" => "KGT 0002/II/2023",
        //         "keterangan" => "BELANJAS",
        //         "debet" => "25412",
        //         "kredit" => "351251",
        //         "saldo" => "151511"
        //     ]
        // ];
        $laporan_kas_gantung = $laporankasgantung->getExport($periode);
        foreach($laporan_kas_gantung as $item){
            $item->tanggal = date('d-m-Y', strtotime($item->tanggal));
        }
        return response([
            'data' => $laporan_kas_gantung
            //   'data' => $export
        ]);
    }
}
