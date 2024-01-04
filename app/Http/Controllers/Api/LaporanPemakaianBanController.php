<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPemakaianBan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPemakaianBanController extends Controller
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
        $dari = $request->dari;
        $sampai = $request->sampai;

        // $jenisLaporan = $request->jenislaporan;
        $jenisLaporan = 'ANALISA BAN';
        $posisiAkhir = '';




        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $laporanpemakaianban = new LaporanPemakaianBan();





            return response([
                'data' => $laporanpemakaianban->getReport($dari, $sampai, $posisiAkhir, $jenisLaporan),
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;

        // $jenisLaporan = $request->jenislaporan;
        $jenisLaporan = 'ANALISA BAN';
        $posisiAkhir = '';
        $laporanpemakaianban = new LaporanPemakaianBan();


        $laporan_pemakaian = $laporanpemakaianban->getReport($dari, $sampai, $posisiAkhir, $jenisLaporan);

        return response([
            'data' => $laporan_pemakaian
        ]);
    }
}
