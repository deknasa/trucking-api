<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaporanDataJurnal extends Model
{
    use HasFactory;

    public function getReport()
    {
        $dari = date('Y-m-d', strtotime(request()->dari)) ?? '1900/1/1';
        $sampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900/1/1';
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();
        $data = DB::table('jurnalumumheader AS A')
        ->join('jurnalumumdetail AS B', 'A.nobukti', '=', 'B.nobukti')
        ->join('akunpusat AS C', 'B.coa', '=', 'C.coa')
        ->select(
            'A.nobukti',
            'A.tglbukti',
            DB::raw("CASE WHEN B.nominal >= 0 THEN B.coa ELSE '' END AS CoaDebet"),
            DB::raw("CASE WHEN B.nominal >= 0 THEN '' ELSE B.coa END AS CoaKredit"),
            DB::raw("CASE WHEN B.nominal >= 0 THEN C.keterangancoa ELSE '' END AS KetCoaDebet"),
            DB::raw("CASE WHEN B.nominal >= 0 THEN '' ELSE C.keterangancoa END AS KetCoaKredit"),
            DB::raw("CASE WHEN B.nominal >= 0 THEN B.nominal ELSE 0 END AS Debet"),
            DB::raw("CASE WHEN B.nominal >= 0 THEN 0 ELSE ABS(B.nominal) END AS Kredit"),
            'B.keterangan',
            'A.postingdari',
            'A.keterangan AS FKetHeader',
            DB::raw("'Laporan Data Jurnal' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
        )
        ->whereBetween('A.tglbukti', [$dari, $sampai])
        ->orderBy('A.tglbukti')
        ->orderBy('A.nobukti')
        ->orderBy('B.id')
        ->get();

        return $data;
    }
}
