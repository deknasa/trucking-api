<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Models\LaporanPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LaporanPembelianController extends Controller
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
    public function report(ReportLaporanPembelianRequest $request)
    {

        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $supplierdari = $request->supplierdari;
        $suppliersampai = $request->suppliersampai;
        $supplierdari_id = $request->supplierdari_id;
        $suppliersampai_id = $request->suppliersampai_id;
        $status = $request->status;

        $laporanpembelian = new LaporanPembelian();

        $laporan_pembelian = $laporanpembelian->getReport($dari, $sampai, $supplierdari, $suppliersampai, $supplierdari_id, $suppliersampai_id, $status);



        if ($request->isCheck) {
            if (count($laporan_pembelian) === 0) {
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
            foreach ($laporan_pembelian as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            }
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $laporan_pembelian,
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
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $supplierdari = $request->supplierdari;
        $suppliersampai = $request->suppliersampai;
        $status = $request->status;

        $laporanpembelian = new LaporanPembelian();

        $laporan_pembelian = $laporanpembelian->getExport($dari, $sampai, $supplierdari, $suppliersampai, $status);


        if ($request->isCheck) {
            if (count($laporan_pembelian) === 0) {
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
                'data' => $laporan_pembelian,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
                // 'data' => $Export
            ]);
        }
    }
}
