<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanArusDanaPusat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanArusDanaPusatController extends Controller
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

    public function mingguan()
    {

        $laporanArusDanaPusat = new LaporanArusDanaPusat();

        return response([
            'data' => $laporanArusDanaPusat->getMingguan(),
            'attributes' => [
                'totalRows' => $laporanArusDanaPusat->totalRows,
                'totalPages' => $laporanArusDanaPusat->totalPages
            ]
        ]);
    }
    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {

        $tgldari = $request->tgldari  ?? '01-01-1900';
        $tglsampai = $request->tglsampai ?? '01-01-1900';
        $cabang_id = $request->cabang_id ?? 0;
        $minggu = $request->minggu ?? '';
        $laporanArusDanaPusat = new LaporanArusDanaPusat();
        // $data1=$laporanArusDanaPusat->getReport($tgldari, $tglsampai, $cabang_id, $minggu);
        // dd($data1);
        
        return response([
            'data' => $laporanArusDanaPusat->getReport($tgldari, $tglsampai, $cabang_id, $minggu),
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $tgldari = $request->tgldari;
        $tglsampai = $request->tglsampai;
        $cabang_id = $request->cabang_id;
        $minggu = $request->minggu;

        $laporanArusDanaPusat = new LaporanArusDanaPusat();
        return response([
            'data' => $laporanArusDanaPusat->getReport($tgldari, $tglsampai, $cabang_id, $minggu),
        ]);
    }
}
