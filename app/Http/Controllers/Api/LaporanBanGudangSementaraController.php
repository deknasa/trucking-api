<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanBanGudangSementara;

class LaporanBanGudangSementaraController extends Controller
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
    public function report()
    {
        // $report = [
        //     [
        //         "kodestok" => "BAUT 12",
        //         'namastok' => 'BAUT 12',
        //         'gudang' => 'GUDANG PIHAK KE-3',
        //         'nobukti' => 'PG 00035/II/2023',
        //         'tanggal' => '23/2/2023',
        //         'jlhhari' => '23'
        //     ]
        // ];
        // return response([
        //     'data' => $report
        // ]);


        $laporanbankgudangsementara = new LaporanBanGudangSementara();

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
        ->select('cabang.namacabang')
        ->join("parameter", 'parameter.text', 'cabang.id')
        ->where('parameter.grp', 'ID CABANG')
        ->first();


        return response([
            'data' => $laporanbankgudangsementara->getReport(),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }
      /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export()
    {
        $laporanbankgudangsementara = new LaporanBanGudangSementara();
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporanbankgudangsementara->getReport(),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }
}
