<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Http\Requests\ValidasiLaporanKartuHutangPerSupplierRequest;
use App\Models\LaporanKartuHutangPerSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LaporanKartuHutangPerSupplierController extends Controller
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
    public function report(ValidasiLaporanKartuHutangPerSupplierRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $supplierdari = $request->supplierdari_id ?? 0;
        $suppliersampai = $request->suppliersampai_id ?? 0;
        $prosesneraca=0;

        $laporankartuhutangpersupplier = new LaporanKartuHutangPerSupplier();

        $laporan_kartuhutangpersupplier = $laporankartuhutangpersupplier->getReport($dari, $sampai, $supplierdari, $suppliersampai,$prosesneraca);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
        ->select('cabang.namacabang')
        ->join("parameter", 'parameter.text', 'cabang.id')
        ->where('parameter.grp', 'ID CABANG')
        ->first();
        // if ($request->isCheck) {
        //     if (count($laporan_kartuhutangpersupplier) === 0) {
        //         return response([
        //             'errors' => [
        //                 "export" => app(ErrorController::class)->geterror('DTA')->keterangan
        //             ],

        //             'message' => "The given data was invalid."
        //         ], 422);
        //     } else {
        //         return response([
        //             'data' => 'ok'
        //         ]);
        //     }
        // } else {
            // foreach ($laporan_kartuhutangpersupplier as $item) {
            //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
            // }
            return response([
                'data' => $laporan_kartuhutangpersupplier,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
                // 'data' => $report
            ]);
        // }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanKartuHutangPerSupplierRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $supplierdari = $request->supplierdari_id ?? 0;
        $suppliersampai = $request->suppliersampai_id ?? 0;
        $prosesneraca=0;

        $laporankartuhutangpersupplier = new LaporanKartuHutangPerSupplier();
        $laporan_kartuhutangpersupplier = $laporankartuhutangpersupplier->getReport($dari, $sampai, $supplierdari, $suppliersampai,$prosesneraca);

        if ($request->isCheck) {
            if (count($laporan_kartuhutangpersupplier) === 0) {
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

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
            return response([
                'data' => $laporan_kartuhutangpersupplier,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
                // 'data' => $report
            ]);
        }
    }
}
