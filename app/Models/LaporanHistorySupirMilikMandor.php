<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LaporanHistorySupirMilikMandor extends Model
{
    use HasFactory;

    public function getReport($supir_id)
    {
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $getSupir = DB::table("historysupirmilikmandor")->from(DB::raw("historysupirmilikmandor as a with (readuncommitted)"))
        ->select(
            'a.supir_id',
            'supir.namasupir',
            'a.tglberlaku',
            'mandorlama.namamandor as mandorlama',
            'mandorbaru.namamandor as mandorbaru',
            DB::raw("'Laporan History Supir Milik Mandor' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'a.supir_id', 'supir.id')
        ->leftJoin(DB::raw("mandor as mandorbaru with (readuncommitted)"), 'a.mandor_id', 'mandorbaru.id')
        ->leftJoin(DB::raw("mandor as mandorlama with (readuncommitted)"), 'a.mandorlama_id', 'mandorlama.id')
        ->orderBy('a.supir_id', 'asc')
        ->orderBy('a.tglberlaku', 'desc');

        if($supir_id != 0){
            $getSupir->where('a.supir_id', $supir_id);
        }

        return $getSupir->get();
    }
}
