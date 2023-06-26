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
        
        $periode = date('Y-m-d', strtotime($request->periode));
    
     

        $laporanpiutanggiro = new LaporanPiutangGiro();

       

        $laporan_piutanggiro= $laporanpiutanggiro->getReport($periode);
        foreach($laporan_piutanggiro as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        }
        return response([
            'data' => $laporan_piutanggiro
            // 'data' => $report
        ]);
        }
      
         /**
     * @ClassName
     */
    public function export(Request $request)
    {
        
        $periode = date('Y-m-d', strtotime($request->periode));
    
     

        $laporanpiutanggiro = new LaporanPiutangGiro();

       

        $laporan_piutanggiro= $laporanpiutanggiro->getExport($periode);
        foreach($laporan_piutanggiro as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        }
        return response([
            'data' => $laporan_piutanggiro
            // 'data' => $report
        ]);
        }
      
}
