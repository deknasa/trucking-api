<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ApprovalStokReuse;
use App\Http\Controllers\Controller;

class LaporanApprovalStokReuseController extends Controller
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
        $supir_id = $request->supir_id ?? 0;


        $laporan = new LaporanHistoryTradoMilikSupir();
        return response([
            'data' => $laporan->getReport($supir_id)
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        
        $stok_id = $request->stok_id ?? 0;

        $laporan = new ApprovalStokReuse();
        return response([
            'data' => $laporan->getReport($stok_id)
        ]);
    }
}
