<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKasGantung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKasGantungController extends Controller
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
        $periode = $request->periode;

        // $report = LaporanKasGantung::getReport($sampai, $jenis);
        $report = [
            [
                'tanggal' => "24/2/2023",
                "nobukti" => "KGT 0002/II/2023",
                "keterangan" => "TES KETERANGAN I",
                "debet" => "25412",
                "kredit" => "351251",
                "saldo" => "151511"
            ]
        ];
        return response([
            'data' => $report
        ]);
    }
}
