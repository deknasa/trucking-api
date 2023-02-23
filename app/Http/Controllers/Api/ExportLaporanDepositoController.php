<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportLaporanDeposito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportLaporanDepositoController extends Controller
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
        $periode = $request->periode;

        $report = ExportLaporanDeposito::getExport($periode);
        return response([
            'data' => $report
        ]);
    }
}
