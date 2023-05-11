<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPenyesuaianBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPenyesuaianBarangController extends Controller
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


        $report = LaporanPenyesuaianBarang::getReport($dari,$sampai);

        $report = [
            [
                "header"=> "PT. Transporindo Agung Sejahtera",
                "id" => 1,
                "nopolisi" => "BK 1234 DG",
                "noadj" => "SPK 0018/V/2023",
                "tanggal" => "05-05-2023",
                "memo" => " ",
                "kdbarang" => "Dalam Swallow 1000",
                "namabrg" => "SPAREPART-BAN DALAM SWALLOW 1000",
                "gudang" => "GUDANG KANTOR",
                "qty" => "-1000K",
                "satuan" => "Buah",
                "hargasatuan" => "340000",
                "nominal" => "-340000",
            ],
            [
                "header"=> "PT. Transporindo Agung Sejahtera",
                "id" => 1,
                "nopolisi" => "BK 1234 DG",
                "noadj" => "SPK 0018/V/2023",
                "tanggal" => "05-05-2023",
                "memo" => " ",
                "kdbarang" => "Selendang Ban",
                "namabrg" => "SPAREPART-SELENDANG BAN",
                "gudang" => "GUDANG KANTOR",
                "qty" => "-1000K",
                "satuan" => "Buah",
                "hargasatuan" => "350000",
                "nominal" => "-350000",
            ],
            [
                "header"=> "PT. Transporindo Agung Sejahtera",
                "id" => 2,
                "nopolisi" => "BK 3456 RT",
                "noadj" => "SPK 0019/V/2023",
                "tanggal" => "06-05-2023",
                "memo" => " ",
                "kdbarang" => "238643",
                "namabrg" => "SPAREPART-GEMBOK BESAR",
                "gudang" => "GUDANG KANTOR",
                "qty" => "-1000K",
                "satuan" => "Buah",
                "hargasatuan" => "400000",
                "nominal" => "-400000",
            ],
            [
                "header"=> "PT. Transporindo Agung Sejahtera",
                "id" => 2,
                "nopolisi" => "BK 3456 RT",
                "noadj" => "SPK 0019/V/2023",
                "tanggal" => "06-05-2023",
                "memo" => " ",
                "kdbarang" => "02836",
                "namabrg" => "SPAREPART-GEMBOK",
                "gudang" => "GUDANG KANTOR",
                "qty" => "-1000K",
                "satuan" => "Buah",
                "hargasatuan" => "360000",
                "nominal" => "-360000",
            ],
             
            
        ];
        return response([
            'data' => $report
        ]);
    }
    public function export(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;

        $report = LaporanPenyesuaianBarang::getReport($dari,$sampai);

        
        return response([
            'data' => $report
        ]);
    }
}
