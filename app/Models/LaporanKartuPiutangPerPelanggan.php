<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKartuPiutangPerPelanggan extends MyModel
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



    public function getReport($sampai, $dari)
    {

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $sampai = date("Y-m-d", strtotime($sampai));
        // data coba coba
        $query = DB::table('penerimaantruckingdetail')->from(
            DB::raw("penerimaantruckingdetail with (readuncommitted)")
        )->select(
            'penerimaantruckingdetail.id',
            'supir.namasupir',
            'penerimaantruckingdetail.nominal',
            db::raw("'" . $disetujui . "' as disetujui"),
            db::raw("'" . $diperiksa . "' as diperiksa"),
        )
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'penerimaantruckingdetail.supir_id', 'supir.id')
        ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'penerimaantruckingdetail.penerimaantruckingheader_id', 'penerimaantruckingheader.id')
        ->where('penerimaantruckingheader.tglbukti','<=',$sampai);

        $data = $query->get();
        return $data;
    }
}
