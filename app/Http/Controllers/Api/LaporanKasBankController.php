<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanKasBankRequest;
use App\Models\LaporanKasBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKasBankController extends Controller
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
    public function report(ValidasiLaporanKasBankRequest $request)
    {


        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
            $dari = $request->dari;
            $sampai = $request->sampai;
            $bank_id = $request->bank_id;
            $prosesneraca = 0;

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
            $disetujui = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'DISETUJUI')
                ->where('subgrp', 'DISETUJUI')
                ->first();
            $diperiksa = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'DISETUJUI')
                ->where('subgrp', 'DIPERIKSA')
                ->first();
            $dibuat = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'DISETUJUI')
                ->where('subgrp', 'DIBUAT')
                ->first();

            $infoPemeriksa = [
                'dibuat' => $dibuat->text,
                'diperiksa' => $diperiksa->text,
                'disetujui' => $disetujui->text,
            ];

            $laporankasbank = new LaporanKasBank();
            $hasil = $laporankasbank->getReport($dari, $sampai, $bank_id, $prosesneraca);
            return response([
                'data' => $hasil['data'],
                'datasaldo' => $hasil['dataSaldo'],
                'infopemeriksa' => $infoPemeriksa,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanKasBankRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
            $dari = $request->dari;
            $sampai = $request->sampai;
            $bank_id = $request->bank_id;
            $prosesneraca = 0;


            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            $laporankasbank = new LaporanKasBank();
            $hasil = $laporankasbank->getReport($dari, $sampai, $bank_id, $prosesneraca);
            return response([
                'data' => $hasil['data'],
                'namacabang' => 'CABANG ' . $getCabang->namacabang
            ]);
        }
    }
}
