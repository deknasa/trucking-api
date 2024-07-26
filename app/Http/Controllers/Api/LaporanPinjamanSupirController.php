<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanPinjamanSupirRequest;
use App\Models\LaporanPinjamanSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPinjamanSupirController extends Controller
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
    public function report(ValidasiLaporanPinjamanSupirRequest $request)
    {
        $sampai = $request->sampai;
        $jenis = $request->jenis ?? 0;
        $laporanPinjSupir = new LaporanPinjamanSupir();

        $dataPinjSupir = $laporanPinjSupir->getReport($sampai, $jenis);

        if (count($dataPinjSupir) == 0) {
            return response([
                'data' => $dataPinjSupir,
                'message' => 'tidak ada data'
            ], 500);
        } else {
            return response([
                'data' => $dataPinjSupir,
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
        $jenis = $request->jenis;

        $export = LaporanPinjamanSupir::getReport($sampai, $jenis);

        return response([
            'data' => $export
        ]);
    }
}
