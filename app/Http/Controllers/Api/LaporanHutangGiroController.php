<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Models\LaporanHutangGiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LaporanHutangGiroController extends Controller
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
        $periode = date('Y-m-d', strtotime($request->periode));
        $laporanhutanggiro = new LaporanHutangGiro();

        $laporan_hutanggiro = $laporanhutanggiro->getReport($periode);

        if ($request->isCheck) {
            if (count($laporan_hutanggiro) === 0) {

                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],

                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'data' => 'ok'
                ]);
            }
        } else {
            foreach ($laporan_hutanggiro as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
                $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
            }
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $laporan_hutanggiro,
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
        $periode = date('Y-m-d', strtotime($request->periode));

        $laporanhutanggiro = new LaporanHutangGiro();

        $laporan_hutanggiro = $laporanhutanggiro->getReport($periode);

        if ($request->isCheck) {
            if (count($laporan_hutanggiro) === 0) {

                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],

                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'data' => 'ok'
                ]);
            }
        } else {


            foreach ($laporan_hutanggiro as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            }
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
            return response([
                'data' => $laporan_hutanggiro,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
                // 'data' => $report
            ]);
        }
    }
}
