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
        $prosesneraca=0;
        $periode = date('Y-m-d', strtotime($request->periode)) ;
        
        $laporankasgantung = new LaporanKasGantung();
        
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
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
        // return response([
        //     'data' => $laporankasgantung->getReport($periode, $prosesneraca)
        //     // 'data' => $report
        // ]);

        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
 
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
            return response([
                'data' => $laporankasgantung->getReport($periode, $prosesneraca),
                'namacabang' => 'CABANG ' . $getCabang->namacabang
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request){
        $prosesneraca=0;
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
        $prosesneraca=0;
        $laporan_kas_gantung = $laporankasgantung->getReport($periode, $prosesneraca);
        foreach($laporan_kas_gantung as $item){
            $item->tanggal = date('d-m-Y', strtotime($item->tanggal));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporan_kas_gantung,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            //   'data' => $export
        ]);
    }
}
