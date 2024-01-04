<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportRincianMingguanPendapatanSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportRincianMingguanPendapatanSupirController extends Controller
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
        $periode = $request->periode;

        $report = ExportRincianMingguanPendapatanSupir::getExport($periode);
        return response([
            'data' => $report
        ]);
    }
}
