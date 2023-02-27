<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKasBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKasBankController extends Controller
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
        $dari = $request->dari;
        $sampai = $request->sampai;
        $bank_id = $request->bankid;

        // $report = LaporanKasBank::getReport($dari, $sampai, $bank_id);
        $report = [
            [
                "namabank" => "BCA 5125151",
                "saldo_awal" => "47346223",
                "nobukti" => "BKT-M BCA 0001/II/2023",
                "nama_perkiraan" => "BCA 2515124",
                "keterangan" => "TES KETERANGAN",
                "debet" => "1254125",
                "kredit" => "25112512",
                "saldo" => "215125125"
            ]
        ];
        return response([
            'data' => $report
        ]);
    }
}
