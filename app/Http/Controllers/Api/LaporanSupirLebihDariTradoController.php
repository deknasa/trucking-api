<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanSupirLebihDariTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class LaporanSupirLebihDariTradoController extends Controller
{
      /**
     * @ClassName
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        
        $laporansupirlebihdaritrado = new LaporanSupirLebihDariTrado();
        return response([
            'data' => $laporansupirlebihdaritrado->get(),
            'attributes' => [
                'totalRows' => $laporansupirlebihdaritrado->totalRows,
                'totalPages' => $laporansupirlebihdaritrado->totalPages
            ]
        ]);
    }


    
    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $dari = date('Y-m-d', strtotime($request->dari));

        $laporansupirlebihdaritrado = new LaporanSupirLebihDariTrado();
        // $report = LaporanSupirLebihDariTrado::getReport($sampai, $dari);
        // $report = [
        //     [
        //         'supir' => 'HERMAN',
        //         'trado' => 'BK 1252 AJS',
        //         'tanggal' => '23/2/2023',
        //         'ritasi' => '1'
        //     ],[
        //         'supir' => 'HERMAN',
        //         'trado' => 'BK 2415 BNM',
        //         'tanggal' => '23/2/2023',
        //         'ritasi' => '2'
        //     ],
        // ];
        $laporansupirlebih_daritrado = $laporansupirlebihdaritrado->getReport($dari, $sampai);
        foreach($laporansupirlebih_daritrado as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporansupirlebih_daritrado,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $dari = date('Y-m-d', strtotime($request->dari));

        $laporansupirlebihdaritrado = new LaporanSupirLebihDariTrado();
        // $report = LaporanSupirLebihDariTrado::getReport($sampai, $dari);
        // $report = [
        //     [
        //         'supir' => 'HERMAN',
        //         'trado' => 'BK 1252 AJS',
        //         'tanggal' => '23/2/2023',
        //         'ritasi' => '1'
        //     ],[
        //         'supir' => 'HERMAN',
        //         'trado' => 'BK 2415 BNM',
        //         'tanggal' => '23/2/2023',
        //         'ritasi' => '2'
        //     ],
        // ];
        $laporansupirlebih_daritrado = $laporansupirlebihdaritrado->getReport($dari, $sampai);
        foreach($laporansupirlebih_daritrado as $item){
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporansupirlebih_daritrado,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
    }
}
