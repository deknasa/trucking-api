<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPinjamanPerUnitTrado extends MyModel
{
    use HasFactory;

    protected $table = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getReport($trado_id)
    {
        $pengeluaranTrucking = DB::table("pengeluarantrucking")->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
            ->where('kodepengeluaran', 'KLAIM')
            ->first();
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table("pengeluarantruckingheader")->from(DB::raw("pengeluarantruckingheader as A with (readuncommitted)"))
            ->select(
                'C.kodetrado as nopolisi',
                'D.namasupir',
                'A.pengeluarantrucking_nobukti',
                'A.tglbukti',
                'B.keterangan',
                'B.nominal',
                DB::raw("'Laporan Pinjaman Per Unit Trado' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->join(DB::raw("pengeluarantruckingdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw("trado as C with (readuncommitted)"), 'A.trado_id', 'C.id')
            ->join(DB::raw("supir as D with (readuncommitted)"), 'A.supir_id', 'D.id')
            ->where('A.pengeluarantrucking_id', $pengeluaranTrucking->id)
            ->where('A.trado_id', $trado_id)
            ->orderBy('D.namasupir');

        return $query->get();
    }
}
