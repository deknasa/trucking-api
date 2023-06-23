<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Models\LaporanPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LaporanPembelianController extends Controller
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
    public function report(ReportLaporanPembelianRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $supplierdari = $request->supplierdari;
        $suppliersampai = $request->suppliersampai;
        $status = $request->status;

        $laporanpembelian = new LaporanPembelian();

       

        $laporan_pembelian= $laporanpembelian->getReport($dari, $sampai,$supplierdari, $suppliersampai, $status );
        foreach($laporan_pembelian as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
      
        return response([
            'data' => $laporan_pembelian
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
        $supplierdari = $request->supplierdari;
        $suppliersampai = $request->suppliersampai;
        $status = $request->status;

        $laporanpembelian = new LaporanPembelian();
        // if ($sampai < $dari) {
        //     return response()->json(['error' => 'Tanggal Sampai tidak boleh lebih kecil dari Tanggal Dari'], 400);
        // }
        $laporan_pembelian= $laporanpembelian->getExport($dari, $sampai,$supplierdari, $suppliersampai, $status );

      
        return response([
            'data' => $laporan_pembelian
            // 'data' => $Export
        ]);
    }
    
}
