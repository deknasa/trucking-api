<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanHutangBBM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanHutangBBMController extends Controller
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

        $report = LaporanHutangBBM::getReport($sampai);

        // $report = [
        //     [
        //         "tanggal" => "23/02/2023",
        //         "keterangan" => "TES KETERANGAN 1",
        //         "nominal" => "2151251",
        //         "saldo" => "125153"
        //     ],
        //     [
        //         "tanggal" => "23/02/2023",
        //         "keterangan" => "TES KETERANGAN 2",
        //         "nominal" => "6134151",
        //         "saldo" => "263467312"
        //     ],
        //     [
        //         "tanggal" => "23/02/2023",
        //         "keterangan" => "TES KETERANGAN 3",
        //         "nominal" => "7457246",
        //         "saldo" => "1261631"
        //     ],
        // ];
        return response([
            'data' => $report
        ]);
    }

      /**
     * @ClassName
     */
    public function export(Request $request)
        {
            $sampai = $request->sampai;
    
            $export = LaporanHutangBBM::getReport($sampai);

            foreach ($export as $data) {
           
                $data->tanggal = date('d-m-Y', strtotime($data->tanggal));
            }

    
    
            return response([
                'data' => $export
            ]);
        }
}
