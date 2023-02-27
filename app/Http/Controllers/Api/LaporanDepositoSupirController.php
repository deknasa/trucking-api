<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanDepositoSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanDepositoSupirController extends Controller
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
    public function report(Request $request)
    {
        $sampai = $request->sampai;
        $jenis = $request->jenis;

        // $report = LaporanDepositoSupir::getReport($sampai, $jenis);
        $report = [
            [
                "nobukti" => "DPO 0001/II/2023",
                "supir" => "HERMAN",
                "keterangan" => "COBA KETERANGAN TES",
                "nominal" => "2123112",
                "nominal_deposito" => "2412312",
                "penarikan" => "12312",
                "total" => "123144",
                "mencapai" => "LIMA JUTA"
            ]
        ];
        return response([
            'data' => $report
        ]);
    }
}
