<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Http\Requests\ValidasiLaporanKartuPiutangPerAgenRequest;
use App\Models\LaporanKartuPiutangPerAgen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LaporanKartuPiutangPerAgenController extends Controller
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
    public function report(ValidasiLaporanKartuPiutangPerAgenRequest $request)
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
            $prosesneraca = 0;


            $laporankartupiutangperagen = new LaporanKartuPiutangPerAgen();


            $laporan_piutangperagen = $laporankartupiutangperagen->getReport($dari, $sampai, $agendari, $agensampai, $prosesneraca);
            // foreach ($laporan_piutangperagen as $item) {
            //     // $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            // }
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $laporan_piutangperagen,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
                // 'data' => $report
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanKartuPiutangPerAgenRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $agendari = $request->agendari_id;
        $agensampai = $request->agensampai_id;
        $prosesneraca = 0;

        $laporankartupiutangperagen = new LaporanKartuPiutangPerAgen();


        $laporan_piutangperagen = $laporankartupiutangperagen->getReport($dari, $sampai, $agendari, $agensampai, $prosesneraca);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporan_piutangperagen,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
    }
}
