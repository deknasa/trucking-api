<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanHistoryPinjaman;
use App\Models\Supir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanHistoryPinjamanController extends Controller
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
        $supirdari_id = $request->supirdari_id ?? 0;
        $supirsampai_id = $request->supirsampai_id ?? 0;
        $supirdari = ($supirdari_id != '') ? Supir::find($supirdari_id) : '';
        $supirsampai = ($supirsampai_id != '') ? Supir::find($supirsampai_id) : '';

        $laporanhistorypinjaman = new LaporanHistoryPinjaman();

        $laporan_historypinjaman = $laporanhistorypinjaman->getReport($supirdari_id, $supirsampai_id);
        foreach ($laporan_historypinjaman as $item) {
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            if ($supirdari_id != '') {
                $item->supirdari = $supirdari->namasupir;
                $item->supirsampai = $supirsampai->namasupir;
            }
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();


        return response([
            'data' => $laporan_historypinjaman,
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
        $supirdari_id = $request->supirdari_id ?? '';
        $supirsampai_id = $request->supirsampai_id ?? '';
        $supirdari = ($supirdari_id != '') ? Supir::find($supirdari_id) : '';
        $supirsampai = ($supirsampai_id != '') ? Supir::find($supirsampai_id) : '';

        $laporanhistorypinjaman = new LaporanHistoryPinjaman();


        $laporan_historypinjaman = $laporanhistorypinjaman->getReport($supirdari_id, $supirsampai_id);
        foreach ($laporan_historypinjaman as $item) {
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();


        return response([
            'data' => $laporan_historypinjaman,
            'supirdari' => ($supirdari_id != '') ? $supirdari->namasupir : 'SEMUA',
            'supirsampai' => ($supirsampai_id != '') ? $supirsampai->namasupir : 'SEMUA',
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
    }
}
