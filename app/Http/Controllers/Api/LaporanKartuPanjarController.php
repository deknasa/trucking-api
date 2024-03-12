<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LaporanKartuPanjar;
use App\Http\Requests\StoreLaporanKartuPanjarRequest;
use App\Http\Requests\UpdateLaporanKartuPanjarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ValidasiLaporanKartuPanjarRequest;


class LaporanKartuPanjarController extends Controller
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
    public function report(ValidasiLaporanKartuPanjarRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
            $dari = date('Y-m-d', strtotime($request->dari));
            $sampai = date('Y-m-d', strtotime($request->sampai));
            $agendari = $request->agendari_id ?? 0;
            $agensampai = $request->agensampai_id ?? 0;
            $prosesneraca=0;


            $laporankartupanjar = new LaporanKartuPanjar();


            $laporan_panjar = $laporankartupanjar->getReport($dari, $sampai, $agendari, $agensampai,$prosesneraca);
            // foreach ($laporan_panjar as $item) {
            //     // $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            // }

            return response([
                'data' => $laporan_panjar
                // 'data' => $report
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanKartuPanjarRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $agendari = $request->agendari_id;
        $agensampai = $request->agensampai_id;
        $prosesneraca=0;

        $laporankartupanjar = new LaporanKartuPanjar();


        $laporan_panjar = $laporankartupanjar->getReport($dari, $sampai, $agendari, $agensampai,$prosesneraca);

        return response([
            'data' => $laporan_panjar
            // 'data' => $report
        ]);
    }

}
