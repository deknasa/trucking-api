<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanWarkatBelumCair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanWarkatBelumCairController extends Controller
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


        // $report = LaporanWarkatBelumCair::getReport($periode);
        $report = [
            [
                "nowarkat" => "DX 732593",
                "type" => "HUTANG GIRO",
                "nominal" => "7454900",
                "tanggaljt" => "07-04-2023",
                "memo" => "PEMBAYARAN ATAS PEMBELIAN KEPADA SAUDARA MOTOR",
            ],
            [
                "nowarkat" => "DX 732594",
                "type" => "HUTANG GIRO",
                "nominal" => "10000000",
                "tanggaljt" => "07-04-2023",
                "memo" => "PEMBAYARAN ATAS PEMBELIAN KEPADA ACHING",
            ],
            [
                "nowarkat" => "DX 732595",
                "type" => "HUTANG GIRO",
                "nominal" => "10000000",
                "tanggaljt" => "08-04-2023",
                "memo" => "PEMBAYARAN ATAS PEMBELIAN KEPADA SURYA MOTOR",
            ],
           
            
        ];
        return response([
            'data' => $report
        ]);
    }
}
