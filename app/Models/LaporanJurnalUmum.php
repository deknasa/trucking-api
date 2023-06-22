<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanJurnalUmum extends MyModel
{
    use HasFactory;

    protected $table = 'laporanjurnalumum';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getReport($dari, $sampai)
    {
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $select_getJudul = DB::table('jurnalumumpusatheader')->from(DB::raw("jurnalumumpusatheader AS H with (readuncommitted)"))
            ->select(
                'H.nobukti',
                'H.tglbukti',
                'H.keterangan',
                'H.postingdari',
                'H.modifiedby',
                'H.created_at',
                DB::raw('ISNULL(CM.coa, C.coa) AS coa'),
                DB::raw('ISNULL(CM.keterangancoa, C.keterangancoa) AS keterangcoa'),
                DB::raw('CASE SIGN(D.Nominal) WHEN 1 THEN D.Nominal ELSE 0 END AS FDebet'),
                DB::raw('CASE SIGN(D.Nominal) WHEN 1 THEN 0 ELSE ABS(D.Nominal) END AS FCredit'),
                'D.keterangan AS keterangandetail',
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("'$dari' AS dari"),
                DB::raw("'$sampai' AS sampai"),
                DB::raw("'Laporan Jurnal Umum' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak") 
                
            )
            ->join(DB::raw("jurnalumumpusatdetail AS D with (readuncommitted)"), 'D.nobukti', 'H.nobukti')
            ->join(DB::raw("akunpusat AS C with (readuncommitted)"), 'C.coa', 'D.coa')
            ->leftJoin(DB::raw("mainakunpusat AS CM with (readuncommitted)"), 'CM.coa', 'C.coamain')
            ->whereBetween('H.tglbukti', [$dari, $sampai])
            ->orderBy('H.tglbukti')
            ->orderBy('H.nobukti');

   

        // dd($select_getJudul->toSql());
        $data = $select_getJudul->get();
        return $data;
        // dd($select_getJudul);
    }

    public function getExport($dari, $sampai)
    {
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $select_getJudul = DB::table('jurnalumumpusatheader')->from(DB::raw("jurnalumumpusatheader AS H with (readuncommitted)"))
            ->select(
                'H.nobukti',
                'H.tglbukti',
                'H.keterangan',
                'H.postingdari',
                'H.modifiedby',
                'H.created_at',
                DB::raw('ISNULL(CM.coa, C.coa) AS coa'),
                DB::raw('ISNULL(CM.keterangancoa, C.keterangancoa) AS keterangcoa'),
                DB::raw('CASE SIGN(D.Nominal) WHEN 1 THEN D.Nominal ELSE 0 END AS FDebet'),
                DB::raw('CASE SIGN(D.Nominal) WHEN 1 THEN 0 ELSE ABS(D.Nominal) END AS FCredit'),
                'D.keterangan AS keterangandetail',
                DB::raw("'$getJudul->text' AS text"),
                DB::raw("'$dari' AS dari"),
                DB::raw("'$sampai' AS sampai"),
                DB::raw("'Laporan Jurnal Umum' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak") 
                
            )
            ->join(DB::raw("jurnalumumpusatdetail AS D with (readuncommitted)"), 'D.nobukti', 'H.nobukti')
            ->join(DB::raw("akunpusat AS C with (readuncommitted)"), 'C.coa', 'D.coa')
            ->leftJoin(DB::raw("mainakunpusat AS CM with (readuncommitted)"), 'CM.coa', 'C.coamain')
            ->whereBetween('H.tglbukti', [$dari, $sampai])
            ->orderBy('H.tglbukti')
            ->orderBy('H.nobukti');

   

        // dd($select_getJudul->toSql());
        $data = $select_getJudul->get();
        return $data;
        // dd($select_getJudul);
    }

}
