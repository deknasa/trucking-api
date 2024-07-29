<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\LaporanSupplierBandingStok;

class LaporanSupplierBandingStokController extends Controller
{
     /**
     * @ClassName 
     * LaporanSupplierBandingStok
     * @Keterangan Tampilan index
     */
    public function index() {

    }
     /**
     * @ClassName 
     * LaporanSupplierBandingStok
     * @Keterangan Cetak Laporan
     */
    public function report(Request $request) {

        $laporanSupplierBandingStok = new LaporanSupplierBandingStok;
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
            'data' => $laporanSupplierBandingStok->getStokBySupplier($request->supplier_id),
            'judul' => $getJudul->text,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
        return $request->all();
    }
}
