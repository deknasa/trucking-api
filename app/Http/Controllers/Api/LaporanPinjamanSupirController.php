<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPinjamanSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPinjamanSupirController extends Controller
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

        
        // $report = [
        //     [
        //         "supir" => "HERMAN",
        //         "keterangan" => "TES KETERANGAN",
        //         "nominal" => "351251",
        //         "nominal_pinjaman" => "124124",
        //         "pengembalian" => "2112312",
        //         "saldo" => "12512512"
        //     ],
        //     [
        //         "supir" => "ANDIKA",
        //         "keterangan" => "TES KETERANGAN ANDIKA",
        //         "nominal" => "4125151",
        //         "nominal_pinjaman" => "461123",
        //         "pengembalian" => "512515",
        //         "saldo" => "52612463"
        //     ]

        // ];

        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $sampai = $request->sampai;

            $report = LaporanPinjamanSupir::getReport($sampai);

            return response([
                'data' => $report
            ]);
        }


    }
}