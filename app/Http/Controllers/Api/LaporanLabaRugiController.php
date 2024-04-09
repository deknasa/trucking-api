<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanLabaRugiRequest;
use App\Models\Cabang;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanLabaRugi;

class LaporanLabaRugiController extends Controller
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
    public function report(ValidasiLaporanLabaRugiRequest $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);
        $cabang_id = $request->cabang_id ?? 0;

        $laporanlabarugi = new LaporanLabaRugi();

        $laporan_labarugi = $laporanlabarugi->getReport($bulan, $tahun, $cabang_id);

        $cabang = Cabang::find($request->cabang_id);
        $dataHeader = [
            'cabang' => ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang
        ];

        if (count($laporan_labarugi) == 0) {
            return response([
                'data' => $laporan_labarugi,
                'message' => 'tidak ada data'
            ], 500);
        } else {
            return response([
                'data' => $laporan_labarugi,
                'dataheader' => $dataHeader,
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
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);
        $cabang_id = $request->cabang_id ?? 0;

        $laporanlabarugi = new LaporanLabaRugi();


        $laporan_labarugi = $laporanlabarugi->getReport($bulan, $tahun, $cabang_id);
        // foreach($laporan_labarugi as $item){
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }

        if (count($laporan_labarugi) == 0) {
            return response([
                'data' => $laporan_labarugi,
                'message' => 'tidak ada data'
            ], 500);
        } else {

            $cabang = Cabang::find($request->cabang_id);
            $dataHeader = [
                'cabang' => ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang
            ];
            return response([
                'data' => $laporan_labarugi,
                'dataheader' => $dataHeader,
                'message' => 'berhasil'
            ]);
        }
    }
}
