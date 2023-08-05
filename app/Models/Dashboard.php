<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dashboard extends MyModel
{
    use HasFactory;

    public function getTrado()
    {
        $aktif = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS AKTIF')->where("text", 'AKTIF')->first();
        $nonAktif = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS AKTIF')->where("text", 'NON AKTIF')->first();

        $tradoAktif = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))->where('statusaktif', $aktif->id)->count();
        $tradoNonAktif = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))->where('statusaktif', $nonAktif->id)->count();
        $supirAktif = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->where('statusaktif', $aktif->id)->count();
        $supirNonAktif = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->where('statusaktif', $nonAktif->id)->count();

        $data = [
            'tradoaktif' => $tradoAktif,
            'tradononaktif' => $tradoNonAktif,
            'supiraktif' => $supirAktif,
            'supirnonaktif' => $supirNonAktif,
        ];
        return $data;
    }
}
