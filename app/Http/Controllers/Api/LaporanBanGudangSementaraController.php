<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanBanGudangSementaraController extends Controller
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
    public function report()
    {
        $report = [
            [
                "kodestok" => "BAUT 12",
                'namastok' => 'BAUT 12',
                'gudang' => 'GUDANG PIHAK KE-3',
                'nobukti' => 'PG 00035/II/2023',
                'tanggal' => '23/2/2023',
                'jlhhari' => '23'
            ]
        ];
        return response([
            'data' => $report
        ]);
    }
}
