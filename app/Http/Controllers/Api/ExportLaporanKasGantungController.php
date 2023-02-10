<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportLaporanKasGantung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportLaporanKasGantungController extends Controller
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

    public function export(Request $request)
    {
        $periode = $request->periode;

        $report = ExportLaporanKasGantung::getExport($periode);
        return response([
            'data' => $report
        ]);
    }
}
