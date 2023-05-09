<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKeteranganPinjamanSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKeteranganPinjamanSupirController extends Controller
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
        $jenis = $request->jenis;


        $report = LaporanKeteranganPinjamanSupir::getReport($periode, $jenis);
        // $report = [
        //     [
        //         "tanggal" => "23/2/2023",
        //         "nobukti" => "PJT 0001/II/2023",
        //         "keterangan" => "Gaji Minus Supir Ady Gunawan BK 8747 BU Tgl. 08 Februari 2023",
        //         "debet" => "215125",
        //         "kredit" => "346436",
        //         "saldo" => "1512512"
        //     ]
        // ];
        return response([
            'data' => $report
        ]);
    }
}