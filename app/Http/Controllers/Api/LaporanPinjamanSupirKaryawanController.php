<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanPinjamanSupirRequest;
use App\Models\LaporanPinjamanSupirKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPinjamanSupirKaryawanController extends Controller
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
    public function report(ValidasiLaporanPinjamanSupirRequest $request)
    {
        $sampai = $request->sampai;
        $laporanPinjSupir = new LaporanPinjamanSupirKaryawan();
        $prosesneraca = 0;

        $dataPinjSupir = $laporanPinjSupir->getReport($sampai, $prosesneraca);

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
     */
    public function export(Request $request)
    {

        $sampai = $request->sampai;
        $prosesneraca = 0;

        $export = LaporanPinjamanSupirKaryawan::getReport($sampai, $prosesneraca);

        return response([
            'data' => $export
        ]);
    }
}
