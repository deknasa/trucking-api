<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanTransaksiHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanTransaksiHarianController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;

        $report = LaporanTransaksiHarian::getReport($dari,$sampai);

        // $report = [
        //     [
        //         "header" => "Pt.Transporindo Agung Sejahtera",
        //         "id" => 1,
        //         "nobukti" => "EBS 0001/V/2023",
        //         "tanggal" => "05-05-2023",
        //         "akunpusat" => "B.LAP-BORONGAN",
        //         "keterangan" => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Ducimus voluptas ut, a perspiciatis voluptate impedit inventore! Aliquid commodi maiores",
        //         "debet" => "1000000",
        //         "kredit" => "",
        //     ],
             
        //     [
        //         "header" => "Pt.Transporindo Agung Sejahtera",
        //         "id" => 2,
        //         "nobukti" => "EBS 0001/V/2023",
        //         "tanggal" => "06-05-2023",
        //         "akunpusat" => "B.LAP-BORONGAN",
        //         "keterangan" => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Ducimus voluptas ut, a perspiciatis voluptate impedit inventore! Aliquid commodi maiores",
        //         "debet" => "",
        //         "kredit" => "1000000",
        //     ],
           
        // ];
        return response([
            'data' => $report
        ]);
    }
    public function export(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;

        $export = LaporanTransaksiHarian::getReport($dari,$sampai);

        foreach ($export as $data) {
           
            $data->tanggal = date('d-m-Y', strtotime($data->tanggal));
        }

        return response([
            'data' => $export
        ]);
    }
}
