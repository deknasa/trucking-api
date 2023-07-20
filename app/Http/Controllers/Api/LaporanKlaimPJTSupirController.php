<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKlaimPJTSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKlaimPJTSupirController extends Controller
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
        $dari = $request->dari;
        $kategori = $request->kelompok;

        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {


            $report = LaporanKlaimPJTSupir::getReport($sampai,$dari,$kategori);

            return response([
                'data' => $report
            ]);
        }


        // $report = LaporanKlaimPJTSupir::getReport($sampai, $dari);
        // $report = [
        //     [
        //         'noklaim' => "1231",
        //         'tanggal' => "23/2/2023",
        //         'nilaiklaim' => '1242155',
        //         'nobukti' => "PJT 0001/II/2023",
        //         'keterangan' => "TES KETERANGAN PROIDENT REPREHENDE",
        //         'bebanke' => '1',
        //         'kodestok' => 'BAUT 12',
        //         'keteranganstok' => 'TEMPORE NIHIL ET ET'
        //     ]
        // ];
        // return response([
        //     'data' => $report
        // ]);
    }
}
