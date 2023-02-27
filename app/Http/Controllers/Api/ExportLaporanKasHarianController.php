<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportLaporanKasHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportLaporanKasHarianController extends Controller
{
    /**
     * @ClassName
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
     */
    public function export(Request $request)
    {
        $sampai = $request->sampai;
        $jenis = $request->jenis;

        $export = ExportLaporanKasHarian::getExport();
        
        return response([
            'data' => $export
        ]);
    }
}
