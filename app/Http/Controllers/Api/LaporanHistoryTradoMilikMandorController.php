<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanHistoryTradoMilikMandor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanHistoryTradoMilikMandorController extends Controller
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
        $trado_id = $request->trado_id ?? 0;


        $laporan = new LaporanHistoryTradoMilikMandor();
        return response([
            'data' => $laporan->getReport($trado_id)
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $trado_id = $request->trado_id ?? 0;

        $laporan = new LaporanHistoryTradoMilikMandor();
        return response([
            'data' => $laporan->getReport($trado_id)
        ]);
    }
}
