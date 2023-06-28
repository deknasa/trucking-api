<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Models\LaporanKartuPiutangPerAgen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LaporanKartuPiutangPerAgenController extends Controller
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
        $supplierdari = $request->supplierdari_id;
        $suppliersampai = $request->suppliersampai_id;


        $laporankartupiutangperagen = new LaporanKartuPiutangPerAgen();


        $laporan_piutangperagen= $laporankartupiutangperagen->getReport($dari, $sampai, $supplierdari, $suppliersampai);
        foreach($laporan_piutangperagen as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        }
      
        return response([
            'data' => $laporan_piutangperagen
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
        $supplierdari = $request->supplierdari_id;
        $suppliersampai = $request->suppliersampai_id;


        $laporankartupiutangperagen = new LaporanKartuPiutangPerAgen();


        $laporan_piutangperagen= $laporankartupiutangperagen->getExport($dari, $sampai, $supplierdari, $suppliersampai);
        foreach($laporan_piutangperagen as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        }
      
        return response([
            'data' => $laporan_piutangperagen
            // 'data' => $report
        ]);
       
    }
    
}
