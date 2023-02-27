<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPemotonganPinjamanDeposito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPemotonganPinjamanDepoController extends Controller
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
        $dari = $request->dari;

        // $report = LaporanPemotonganPinjamanDeposito::getReport($sampai, $dari);
        $report = [
            [
                "supir" => "LIBERTO",
                'status' => '1',
                'jumlah' => '515156',
                'tanggal' => '23/2/2023',
                'notransaksi' => 'KMT 00014/II/2023'
            ]
        ];
        return response([
            'data' => $report
        ]);
    }
}
