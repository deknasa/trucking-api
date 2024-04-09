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

        $supirdari_id = $request->supirdari_id;
        $supirdari = $request->supirdari;
        $laporanhistorydeposito = new LaporanHistoryDeposito();


        $laporan_historydeposito= $laporanhistorydeposito->getReport($supirdari_id);
        foreach($laporan_historydeposito as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();


      
        return response([
            'data' => $laporan_historydeposito,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
}

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request){
        $supirdari_id = $request->supirdari_id;
        $supirdari = $request->supirdari;
        $laporanhistorydeposito = new LaporanHistoryDeposito();


        $laporan_historydeposito= $laporanhistorydeposito->getReport($supirdari_id);
        foreach($laporan_historydeposito as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();


      
        return response([
            'data' => $laporan_historydeposito,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
    }
}