<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ExportLaporanKasHarian;
use App\Http\Requests\ValidasiExportKasHairanRequest;
use App\Http\Requests\ValidasiExportKasHarianRequest;
use App\Http\Requests\ValidasiReportKasHarianRequest;

class ExportLaporanKasHarianController extends Controller
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
    public function export(ValidasiExportKasHarianRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $sampai = $request->periode;
            $jenis = $request->bank_id;


            $export = ExportLaporanKasHarian::getExport($sampai, $jenis);

          
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            foreach ($export[0] as $data) {
                $data->tgl = date('d-m-Y', strtotime($data->tgl));
            }

            return response([
                'data' => $export[0],
                'dataDua' => $export[1],
                'namacabang' => 'CABANG ' . $getCabang->namacabang,
                'namacabang2' =>  $getCabang->namacabang
            ]);
        }
    }
     
    /**
     * @ClassName
     * @Keterangan REPORT REKAP
     */
    public function report(ValidasiReportKasHarianRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $sampai = $request->periode;
            $jenis = $request->bank_id;


            $export = ExportLaporanKasHarian::getExport($sampai, $jenis);

          
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
            $direktur = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp','JABATAN')
                ->where('subgrp','DIREKTUR')
                ->first();
            $gm = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp','JABATAN')
                ->where('subgrp','GENERAL MANAGER')
                ->first();
            $manTrucking = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp','JABATAN')
                ->where('subgrp','MANAGER TRUCKING')
                ->first();
            $kasir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp','JABATAN')
                ->where('subgrp','KASIR')
                ->first();
                
            foreach ($export[0] as $data) {
                $data->tgl = date('d-m-Y', strtotime($data->tgl));
            }
            return response([
                'data' => $export[0],
                'dataDua' => $export[1],
                'namacabang' => 'CABANG ' . $getCabang->namacabang,
                'namacabang2' =>  $getCabang->namacabang,
                'tandatangan' =>  [
                    "direktur"=>$direktur->text,
                    "gm"=>$gm->text,
                    "manTrucking"=>$manTrucking->text,
                    "kasir"=>$kasir->text,
                ]
            ]);
        }
    }
}
