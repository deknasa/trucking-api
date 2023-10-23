<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportRic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportRicController extends Controller
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
        $statusric = $request->statusric;
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $trado_id = $request->trado_id;
        $kelompok_id = $request->kelompok_id;

        $report = (new ExportRic())->getExport($periode, $statusric, $dari, $sampai, $trado_id, $kelompok_id);
        return response([
            'data' => $report
        ]);
    }
}
