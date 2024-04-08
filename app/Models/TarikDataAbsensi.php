<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TarikDataAbsensi extends Model
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


        $query = DB::table($this->table)->from(DB::raw("absensisupirdetail with (readuncommitted)"));

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $query->select(
            "header.nobukti as nobukti",
            "header.tglbukti as tglbukti",
            "trado.kodetrado as trado",
            "supir.namasupir as supir",
            "supir.noktp as noktp",
            "absensisupirdetail.keterangan as keterangan_detail",
            "absentrado.kodeabsen as kodeabsen",
            "absensisupirdetail.uangjalan as uangjalan",
            DB::raw("'Laporan Tarik Data Absensi' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            db::raw("'" . $disetujui . "' as disetujui"),
            db::raw("'" . $diperiksa . "' as diperiksa"),

        )
            ->leftjoin(DB::raw("absensisupirheader as header with (readuncommitted)"), "header.id", "absensisupirdetail.absensi_id")
            ->leftjoin(DB::raw("trado with (readuncommitted)"), "trado.id", "absensisupirdetail.trado_id")
            ->leftjoin(DB::raw("supir with (readuncommitted)"), "supir.id", "absensisupirdetail.supir_id")
            ->leftjoin(DB::raw("absentrado with (readuncommitted)"), "absentrado.id", "absensisupirdetail.absen_id")
            ->whereBetween('header.tglbukti', [$dari, $sampai]);

        return $query->get();
    }
}
