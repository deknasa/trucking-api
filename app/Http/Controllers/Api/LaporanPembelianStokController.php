<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Models\LaporanPembelianStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LaporanPembelianStokController extends Controller
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
        $stokdari = $request->stokdari_id;
        $stoksampai = $request->stoksampai_id;
     

        $laporanpembelianstok = new LaporanPembelianStok();

       

        $laporan_pembelianstok= $laporanpembelianstok->getReport($dari, $sampai,$stokdari, $stoksampai);
        foreach($laporan_pembelianstok as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
      
        return response([
            'data' => $laporan_pembelianstok
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
        $stokdari = $request->stokdari_id;
        $stoksampai = $request->stoksampai_id;

        $laporanpembelianstok = new LaporanPembelianStok();

       

        $laporan_pembelianstok= $laporanpembelianstok->getExport($dari, $sampai,$stokdari, $stoksampai);
        foreach($laporan_pembelianstok as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
      
        return response([
            'data' => $laporan_pembelianstok
            // 'data' => $report
        ]);
    }
    
}
