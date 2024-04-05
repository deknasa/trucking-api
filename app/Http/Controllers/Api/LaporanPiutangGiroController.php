<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanPiutangGiro;

class LaporanPiutangGiroController extends Controller
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

        $laporanpiutanggiro = new LaporanPiutangGiro();

        $laporan_piutanggiro = $laporanpiutanggiro->getReport($periode);

        if ($request->isCheck) {
            if (count($laporan_piutanggiro) === 0) {
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

            foreach ($laporan_piutanggiro as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
                $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
            }

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $laporan_piutanggiro,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
                // 'data' => $report
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

        $laporanpiutanggiro = new LaporanPiutangGiro();

        $laporan_piutanggiro = $laporanpiutanggiro->getExport($periode);

        if ($request->isCheck) {
            if (count($laporan_piutanggiro) === 0) {
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

            foreach ($laporan_piutanggiro as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
                $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
            }

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $laporan_piutanggiro,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
                // 'data' => $report
            ]);
        }
    }
}
