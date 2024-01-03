<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LaporanHistoryTradoMilikMandor extends Model
{
    use HasFactory;
    public function getReport($trado_id)
    {
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table("historytradomilikmandor")->from(DB::raw("historytradomilikmandor as a with (readuncommitted)"))
        ->select(
            'a.trado_id',
            'trado.kodetrado',
            'a.tglberlaku',
            'mandorlama.namamandor as mandorlama',
            'mandorbaru.namamandor as mandorbaru',
            DB::raw("'Laporan History Trado Milik Mandor' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
        ->leftJoin(DB::raw("trado with (readuncommitted)"), 'a.trado_id', 'trado.id')
        ->leftJoin(DB::raw("mandor as mandorbaru with (readuncommitted)"), 'a.mandor_id', 'mandorbaru.id')
        ->leftJoin(DB::raw("mandor as mandorlama with (readuncommitted)"), 'a.mandorlama_id', 'mandorlama.id')
        ->orderBy('a.trado_id', 'asc')
        ->orderBy('a.tglberlaku', 'desc');

        if($trado_id != 0){
            $query->where('a.trado_id', $trado_id);
        }

        return $query->get();
    }
}
