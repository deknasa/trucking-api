<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanBiayaSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanBiayaSupirController extends Controller
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $laporanBiayaSupir = new LaporanBiayaSupir();
        return response([
            'data' => $laporanBiayaSupir->getExport($dari, $sampai)
        ]);
    }
}
