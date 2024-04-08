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
        $periode = $request->periode;
        $jenis = $request->jenis;
        $prosesneraca=0;

        $report = LaporanKeteranganPinjamanSupir::getReport($periode, $jenis,$prosesneraca);
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
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
            'data' => $report,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }
    
       /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $periode = $request->periode;
        $jenis = $request->jenis;
        $prosesneraca=0;


        $export = LaporanKeteranganPinjamanSupir::getReport($periode, $jenis,$prosesneraca);

        foreach ($export as $data) {
           
            $data->tanggal = date('d-m-Y', strtotime($data->tanggal));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        return response([
            'data' => $export,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);

       
    }
}