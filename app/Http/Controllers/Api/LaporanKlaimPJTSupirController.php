<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidationForLaporanKlaimPjtSupirRequest;
use App\Models\LaporanKlaimPJTSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKlaimPJTSupirController extends Controller
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
    public function report(ValidationForLaporanKlaimPjtSupirRequest $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;
        $kelompok_id = $request->kelompok_id;
        $laporanKlaim = new LaporanKlaimPJTSupir();

        $laporan_klaim = $laporanKlaim->getReport($sampai,$dari,$kelompok_id);

        if (count($laporan_klaim) == 0) {
            return response([
                'data' => $laporan_klaim,
                'message' => 'tidak ada data'
            ], 500);
        }else{
            return response([
                'data' => $laporan_klaim,
                'message' => 'berhasil'
            ]);
        }
    }
    
    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;
        $kelompok_id = $request->kelompok_id;
        $laporanKlaim = new LaporanKlaimPJTSupir();


        $laporan_klaim = $laporanKlaim->getReport($sampai,$dari,$kelompok_id);

        return response([
            'data' => $laporan_klaim
        ]);
    }
}
