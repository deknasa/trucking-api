<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanArusKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanArusKasController extends Controller
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
        $periode = $request->periode;
        $laporanaruskas = new LaporanArusKas();

        $hasil = $laporanaruskas->getReport($periode);
      
        return response([
            'data' => $hasil['data'],
            'saldo' => $hasil['dataSaldo'],
        ]);
    }
    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $periode = $request->periode;
        $laporanaruskas = new LaporanArusKas();

        $hasil = $laporanaruskas->getReport($periode);
      
        return response([
            'data' => $hasil['data'],
            'saldo' => $hasil['dataSaldo'],
        ]);
    }
}
