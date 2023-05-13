<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPemakaianBan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPemakaianBanController extends Controller
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

        $jenisLaporan = $request->jenislaporan;

        $posisiAkhirTrado = $request->posisiakhirtrado;
        $posisiAkhirGandengan = $request->posisiakhirgandengan;

        if ($posisiAkhirTrado != null) {

            $posisiAkhir = $posisiAkhirTrado;
        }else{
            $posisiAkhir = $posisiAkhirGandengan;
        }

        // $report = LaporanPemakaianBan::getReport($dari, $sampai, $posisiAkhir, $jenisLaporan);
        $report = [
            [
                "header" => 'PT.Transporindo Agung Sejahtera',
                "nobanA" => "45754",
                "jenistrans" => "OUT",
                "nobukti" => "PG 0001/V/2023",
                "tanggal" => "02-05-2023",
                "gudang" => "GUDANG KANTOR",
                "posisiakhir" => "GUDANG PIHAK KE-3",
                "kondisiakhir" => "MENTAH",
                "nopg" => "",
                "nobanB" => "",
                "alasanpenggantian" =>"Vulkanisir",
                "vulke" =>"",
                "noklaim" =>"",
                "nopjt" =>"",
                "ketafkir" =>"",
            ],
            [
                "header" => 'PT.Transporindo Agung Sejahtera',
                "nobanA" => "34237463-3",
                "jenistrans" => "IN",
                "nobukti" => "PG 0002/V/2023",
                "tanggal" => "02-05-2023",
                "gudang" => "GUDANG SEMENTARA",
                "posisiakhir" => "GANDENGAN T-18",
                "kondisiakhir" => "VUL KE 1",
                "nopg" => "PG0002/V/2023",
                "nobanB" => "34237463-3",
                "alasanpenggantian" =>"Vulkanisir",
                "vulke" =>"0",
                "noklaim" =>"",
                "nopjt" =>"",
                "ketafkir" =>"",
            ],
            
            
        ];
        return response([
            'data' => $report
        ]);
    }
}
