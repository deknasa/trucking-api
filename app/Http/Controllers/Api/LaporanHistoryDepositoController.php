<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanHistoryDeposito;

class LaporanHistoryDepositoController extends Controller
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
        $supirdari = $request->supirdari;
        $laporanhistorydeposito = new LaporanHistoryDeposito();


        $laporan_historydeposito= $laporanhistorydeposito->getReport($supirdari_id);
        foreach($laporan_historydeposito as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
      
        return response([
            'data' => $laporan_historydeposito
            // 'data' => $report
        ]);
}

    /**
     * @ClassName
     */
    public function export(Request $request){
        $supirdari_id = $request->supirdari_id;
        $supirdari = $request->supirdari;
        $laporanhistorydeposito = new LaporanHistoryDeposito();


        $laporan_historydeposito= $laporanhistorydeposito->getExport($supirdari_id);
        foreach($laporan_historydeposito as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
      
        return response([
            'data' => $laporan_historydeposito
            // 'data' => $report
        ]);
    }
}