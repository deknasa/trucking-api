<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanUangJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;


class LaporanUangJalanController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        
        $laporanuangjalan = new LaporanUangJalan();
        return response([
            'data' => $laporanuangjalan->get(),
            'attributes' => [
                'totalRows' => $laporanuangjalan->totalRows,
                'totalPages' => $laporanuangjalan->totalPages
            ]
        ]);
    }

    // public function report(Request $request)
    // {
    //     $sampai = $request->sampai;
    //     $jenis = $request->jenis;

    //     $report = LaporanUangJalan::getReport($sampai, $jenis);
    //     return response([
    //         'data' => $report
    //     ]);
    // }
    /**
     * @ClassName
     */
    public function report(Request $request)
    {
          // $report = LaporanUangJalan::getReport($sampai, $jenis);
        // $report = [
        //     [
        //         'namasupir' => "CHANDRA ARIANTO",
        //         "tglabsensi" => "2023/01/30",
        //         "nominalambil" => "1000000",
        //         "tglric" => "2023/02/03",
        //         "nobuktiric" => "RIC 0019/II/2023",
        //         "nominalkembali" => "200000"
        //     ],
        //     [
        //         'namasupir' => "CHANDRA ARIANTO",
        //         "tglabsensi" => "2023/01/30",
        //         "nominalambil" => "1000000",
        //         "tglric" => "2023/02/03",
        //         "nobuktiric" => "RIC 0019/II/2023",
        //         "nominalkembali" => "200000"
        //     ],
        // ];

        $tgldari = date('Y-m-d', strtotime($request->ricdari));
        $tglsampai = date('Y-m-d', strtotime($request->ricsampai));
        $tglambil_jalandari = date('Y-m-d', strtotime($request->ambildari));
        $tglambil_jalansampai = date('Y-m-d', strtotime($request->ambilsampai));
        $supirdari = $request->supirdari;
        $supirsampai = $request->supirsampai;
        $status = $request->status;

        $laporanuangjalan = new LaporanUangJalan();

        $laporan_uang_jalan = $laporanuangjalan->getReport($tgldari, $tglsampai, $tglambil_jalandari, $tglambil_jalansampai, $supirdari, $supirsampai, $status);

        foreach($laporan_uang_jalan as $item){
            $item->tglabsensi = date('d-m-Y', strtotime($item->tglabsensi));
            $item->tglkembali = date('d-m-Y', strtotime($item->tglkembali));
        }
      
        return response([
            'data' => $laporan_uang_jalan
        ]);

    }

    public function export(Request $request){
        $tgldari = date('Y-m-d', strtotime($request->ricdari1));
        $tglsampai = date('Y-m-d', strtotime($request->ricsampai));
        $tglambil_jalandari = date('Y-m-d', strtotime($request->ambildari));
        $tglambil_jalansampai = date('Y-m-d', strtotime($request->ambilsampai));
        $supirdari = $request->supirdari;
        $supirsampai = $request->supirsampai;
        $status = $request->status;

        $laporanuangjalan = new LaporanUangJalan();
       
        $laporan_uang_jalan = $laporanuangjalan->getExport($tgldari, $tglsampai, $tglambil_jalandari, $tglambil_jalansampai, $supirdari, $supirsampai, $status);
     
        // foreach($laporan_uang_jalan as $item){
        //     $item->ricsampai = date('d-m-Y', strtotime($item->ricsampai));
        // }
      
        return response([
            'data' => $laporan_uang_jalan
            //   'data' => $export
        ]);
    }
}
