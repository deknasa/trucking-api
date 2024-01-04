<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanDepositoSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanDepositoSupirController extends Controller
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
        $sampai = $request->sampai;
        $jenis = $request->jenis;
        $prosesneraca=0;

        $laporandepositosupir=new LaporanDepositoSupir();
        
        return response([
            'data' => $laporandepositosupir->getReport($sampai, $jenis,$prosesneraca)
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $sampai = $request->sampai;
        $jenis = $request->jenis;
        $prosesneraca=0;

        $laporandepositosupir=new LaporanDepositoSupir();

        return response([
            'data' => $laporandepositosupir->getReport($sampai, $jenis,$prosesneraca)
        ]);
    }
}
