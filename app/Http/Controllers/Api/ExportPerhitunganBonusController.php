<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ApprovalStokReuse;
use App\Http\Controllers\Controller;
use App\Models\ExportPerhitunganBonus;

class ExportPerhitunganBonusController extends Controller
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
        $supir_id = $request->supir_id ?? 0;

        $laporan = new ExportPerhitunganBonus();
        return response([
            'data' => $laporan->getReport(),
            'dataheader' =>[
                'perkiraan' => 'Perkiraan',
                'bulankesatu' => 'Januari',
                'bulankedua' => 'Februari',
                'bulanketiga' => 'Maret',
            ],
            
            'judul' => "BONUS KARYAWAN JKT JUL s.d SEP",
        ]);
    }
}
