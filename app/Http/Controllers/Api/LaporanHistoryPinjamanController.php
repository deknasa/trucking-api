<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanHistoryPinjaman;
use App\Models\Supir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanHistoryPinjamanController extends Controller
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
        $supirdari_id = $request->supirdari_id;
        $supirsampai_id = $request->supirsampai_id;
        $supirdari = Supir::find($supirdari_id);
        $supirsampai = Supir::find($supirsampai_id);

        $laporanhistorypinjaman = new LaporanHistoryPinjaman();
        
        $laporan_historypinjaman= $laporanhistorypinjaman->getReport($supirdari_id, $supirsampai_id);
        foreach($laporan_historypinjaman as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            $item->supirdari = $supirdari->namasupir;
            $item->supirsampai = $supirsampai->namasupir;
        }
   
        return response([
            'data' => $laporan_historypinjaman
            // 'data' => $report
        ]);
    }


    /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $supirdari_id = $request->supirdari_id;
        $supirsampai_id = $request->supirsampai_id;
        $supirdari = $request->supirdari;
        $supirsampai = $request->supirsampai;

        $laporanhistorypinjaman = new LaporanHistoryPinjaman();


        $laporan_historypinjaman= $laporanhistorypinjaman->getReport($supirdari_id, $supirsampai_id);
        foreach($laporan_historypinjaman as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
   
        return response([
            'data' => $laporan_historypinjaman
            // 'data' => $report
        ]);
      
    }
}
