<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LaporanHistoryTradoMilikSupir extends Model
{
    use HasFactory;
    public function getReport($trado_id)
    {
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table("historytradomiliksupir")->from(DB::raw("historytradomiliksupir as a with (readuncommitted)"))
        ->select(
            'a.trado_id',
            'trado.kodetrado',
            'a.tglberlaku',
            'supirlama.namasupir as supirlama',
            'supirbaru.namasupir as supirbaru',
            DB::raw("'Laporan History Trado Milik Supir' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
        ->leftJoin(DB::raw("trado with (readuncommitted)"), 'a.trado_id', 'trado.id')
        ->leftJoin(DB::raw("supir as supirbaru with (readuncommitted)"), 'a.supir_id', 'supirbaru.id')
        ->leftJoin(DB::raw("supir as supirlama with (readuncommitted)"), 'a.supirlama_id', 'supirlama.id')
        ->orderBy('a.trado_id', 'asc')
        ->orderBy('a.tglberlaku', 'desc');

        if($trado_id != 0){
            $query->where('a.trado_id', $trado_id);
        }

        return $query->get();
    }
}
