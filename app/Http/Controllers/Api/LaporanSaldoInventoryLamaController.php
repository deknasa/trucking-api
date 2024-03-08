<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LaporanSaldoInventoryLama;
use App\Http\Requests\StoreLaporanSaldoInventoryLamaRequest;
use App\Http\Requests\UpdateLaporanSaldoInventoryLamaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanSaldoInventoryLamaController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
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

        $kelompok_id = $request->kelompok_id ?? 0;
        $statusreuse = $request->statusreuse ?? 0;
        $statusban = $request->statusban ?? 0;
        $filter = $request->filter;
        $jenistgltampil = $request->jenistgltampil ?? '';
        $priode = $request->priode;
        $stokdari_id = $request->stokdari_id ?? 0;
        $stoksampai_id = $request->stoksampai_id ?? 0;
        $dataFilter = $request->dataFilter;
        $prosesneraca = 0;

        $laporanSaldoInventoryLama = new LaporanSaldoInventoryLama();
        $report = LaporanSaldoInventoryLama::getReport($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter, $prosesneraca);
          

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $getOpname = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'OPNAME STOK')
            ->where('subgrp', 'OPNAME STOK')
            ->where('text', '3')
            ->first();

        if (isset($getOpname)) {
            $opname = '1';
        } else {
            $opname = '0';
        }

        $queryuser = db::table("user")->from(db::raw("[user] a with (readuncommitted)"))
            ->select(
                'a.cabang_id'
            )
            ->whereraw("a.name='" . auth('api')->user()->name . "'")
            ->where('a.cabang_id', 1)
            ->first();
        if (isset($queryuser)) {
            $opname = '0';
        }

        return response([
            'data' => $report,
            'opname' => $opname,
            'judul' => $getJudul->text
        ]);
    }
    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $kelompok_id = $request->kelompok_id ?? 0;
        $statusreuse = $request->statusreuse ?? 0;
        $statusban = $request->statusban ?? 0;
        $filter = $request->filter;
        $jenistgltampil = $request->jenistgltampil ?? '';
        $priode = $request->priode;
        $stokdari_id = $request->stokdari_id ?? 0;
        $stoksampai_id = $request->stoksampai_id ?? 0;
        $dataFilter = $request->dataFilter;
        $prosesneraca = 0;


        $laporanSaldoInventorylama = new LaporanSaldoInventoryLama();
        $report = LaporanSaldoInventoryLama::getReport($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter, $prosesneraca);

       
        return response([
            'data' => $report
        ]);
    }
}
