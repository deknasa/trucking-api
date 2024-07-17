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
        $jenis = $request->jenis ?? 83;
        $laporanPinjSupir = new LaporanPinjamanSupirKaryawan();
        $prosesneraca = 0;

        $dataPinjSupir = $laporanPinjSupir->getReport($sampai, $prosesneraca,$jenis);

        if (count($dataPinjSupir) == 0) {
            return response([
                'data' => $dataPinjSupir,
                'message' => 'tidak ada data'
            ], 500);
        } else {

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $dataPinjSupir,
                'message' => 'berhasil',
                'namacabang' => 'CABANG ' . $getCabang->namacabang
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
        $jenis = $request->jenis ?? 83;
        $prosesneraca = 0;

        $laporanPinjSupir = new LaporanPinjamanSupirKaryawan();
        $export = $laporanPinjSupir->getReport($sampai, $prosesneraca,$jenis);


        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
        ->select('cabang.namacabang')
        ->join("parameter", 'parameter.text', 'cabang.id')
        ->where('parameter.grp', 'ID CABANG')
        ->first();

        return response([
            'data' => $export,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }
}
