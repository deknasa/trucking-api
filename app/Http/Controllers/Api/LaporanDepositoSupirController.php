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
        $periodedata_id = $request->periodedata_id ?? 0;
        $prosesneraca=0;

        $laporandepositosupir=new LaporanDepositoSupir();
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

            if ($periodedata_id ==665) {
                    $data=$laporandepositosupir->getReportLama($sampai, $jenis,$prosesneraca);
            } else {
                $data=$laporandepositosupir->getReport($sampai, $jenis,$prosesneraca);
            }
        
                // dd('test');
                return response([
                    'data' => $data,
                    'namacabang' => 'CABANG ' . $getCabang->namacabang
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
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        return response([
            'data' => $laporandepositosupir->getReport($sampai, $jenis,$prosesneraca),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }
}
