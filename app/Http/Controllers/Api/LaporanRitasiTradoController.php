<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanRitasiTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanRitasiTradoController extends Controller
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $periode = $request->periode;
        $laporanritasi = new LaporanRitasiTrado();
        // $report = LaporanRitasiTrado::getExport($periode);
        $export = [
            [
                'nopol' => "BK 213121",
            ],

        ];

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        return response([
            'data' => $laporanritasi->getExport($periode),
            'judul' => $getJudul->text,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $export
        ]);
    }
}
