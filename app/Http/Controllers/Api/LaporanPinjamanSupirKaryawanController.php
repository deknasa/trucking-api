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

        $dataPinjSupir = $laporanPinjSupir->getReport($sampai);

        if (count($dataPinjSupir) == 0) {
            return response([
                'data' => $dataPinjSupir,
                'message' => 'tidak ada data'
            ], 500);
        }else{
            return response([
                'data' => $dataPinjSupir,
                'message' => 'berhasil'
            ]);
        }
    }
}
