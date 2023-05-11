<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKartuHutangPerVendorDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LapKartuHutangPerVendorDetailController extends Controller
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

        $supplierDariId = $request->supplierdari_id;
        $supplierSampaiId = $request->suppliersampai_id;

        // $report = LaporanKartuHutangPerVendorDetail::getReport($dari, $sampai, $supplierDariId, $supplierSampaiId);
        $report = [
            [
                "header"=> "PT. Transporindo Agung Sejahtera",
                "supplierid" => "2",
                "namavendor" => "CV.RODA MAS VULKANISIR BAN",
                "nobukti" => "SPB 0001/IV/2023",
                "tanggal" => "01-04-2023",
                "tanggaljt" => "01-04-2023",
                "cicilanke" => "1",
                "nominal" => "4980000",
                "bayar" => "0",
                "saldo" => "4980000",
                "supplierdari" =>"CV.RODA MAS VULKANISIR BAN",
                "suppliersampai" =>"INFO MEDIA"
            ],
            [
                "header"=> "PT. Transporindo Agung Sejahtera",
                "supplierid" => "25",
                "namavendor" => "HOKI JAYA",
                "nobukti" => "SPB 0002/IV/2023",
                "tanggal" => "01-04-2023",
                "tanggaljt" => "01-04-2023",
                "cicilanke" => "1",
                "nominal" => "250000",
                "bayar" => "0",
                "saldo" => "250000",
                "supplierdari" =>"CV.RODA MAS VULKANISIR BAN",
                "suppliersampai" =>"INFO MEDIA"
                
            ],
            [
                "header"=> "PT. Transporindo Agung Sejahtera",
                "supplierid" => "27",
                "namavendor" => "INFO MEDIA",
                "nobukti" => "SPB 0003/IV/2023",
                "tanggal" => "03-04-2023",
                "tanggaljt" => "05-04-2023",
                "cicilanke" => "1",
                "nominal" => "420000",
                "bayar" => "0",
                "saldo" => "420000",
                "supplierdari" =>"CV.RODA MAS VULKANISIR BAN",
                "suppliersampai" =>"INFO MEDIA"

            ],
            
        ];
        return response([
            'data' => $report
        ]);
    }
}
