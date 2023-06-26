<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Models\LaporanHutangGiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LaporanHutangGiroController extends Controller
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
    
     

        $laporanhutanggiro = new LaporanHutangGiro();

       

        $laporan_hutanggiro= $laporanhutanggiro->getReport($periode);
        foreach($laporan_hutanggiro as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
            
        }
      
        return response([
            'data' => $laporan_hutanggiro
            // 'data' => $report
        ]);
    }

   /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $periode = date('Y-m-d', strtotime($request->periode));
    
     

        $laporanhutanggiro = new LaporanHutangGiro();

       

        $laporan_hutanggiro= $laporanhutanggiro->getReport($periode);
        foreach($laporan_hutanggiro as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
      
        return response([
            'data' => $laporan_hutanggiro
            // 'data' => $report
        ]);
       
    }
    
}
