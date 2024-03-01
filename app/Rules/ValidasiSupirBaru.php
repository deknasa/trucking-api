<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;

class ValidasiSupirBaru implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $hari = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS SUPIR BARU LUAR KOTA')->where('subgrp', 'BATAS SUPIR BARU LUAR KOTA')->first()->text ?? '0';

        $idfullempty = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text as id'
            )
            ->where('grp', 'STATUS CONTAINER')
            ->where('subgrp', 'STATUS CONTAINER FULL EMPTY')
            ->first()->id ?? 0;

        $upahsupir = db::table("upahsupir")->from(db::raw("upahsupir with (readuncommitted)"))->where('id', request()->upah_id)->first();
        $batasluarkota = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS KM LUAR KOTA')->where('subgrp', 'BATAS KM LUAR KOTA')->first()->text ?? '0';

        if (request()->statuscontainer_id == $idfullempty) {
            $jarak = $upahsupir->jarakfullempty;
            $jarakbatasluarkota = $batasluarkota * 2;
        } else {
            $jarak = $upahsupir->jarak;
            $jarakbatasluarkota = $batasluarkota;
        }


        			
        $supirluarkota = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LUAR KOTA')->where('subgrp', 'STATUS LUAR KOTA')->where('text', 'BOLEH LUAR KOTA')->first()->id ?? '0';



        $supir = DB::table("supir")->from(DB::raw("supir a with (readuncommitted)"))
            ->where('a.id', request()->supir_id)
            ->whereraw("cast( 
                (case when year(isnull(a.tglmasuk,'1900/1/1'))=1900 then format(getdate(),'yyyy/MM/dd') else isnull(a.tglmasuk,'1900/1/1') end)
                as datetime)+" . $hari . ">=getdate()")
            ->whereraw("a.statusluarkota<>".$supirluarkota)
            ->first();
        if (isset($supir)) {
            if ($jarak>$jarakbatasluarkota ) {
                return false;
            }
        } else {
            $supirall = DB::table("supir")->from(DB::raw("supir a with (readuncommitted)"))
            ->where('a.id', request()->supir_id)
            ->whereraw("a.statusluarkota<>".$supirluarkota)
            ->first();
            if (isset($supirall)) {
                if ($jarak>$jarakbatasluarkota ) {
                    return false;
                }
            } else {
                return true;
            }

            
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $controller = new ErrorController;
        $supir_id = request()->supir_id ?? 0;
        $query = db::table("supir")->from(db::raw("supir a with (readuncommitted)"))
            ->select(
                db::raw("format(cast(isnull(a.tglmasuk,'1900/1/1') as datetime),'dd-MM-yyyy') as tglmasuk")
            )
            ->where('a.id', $supir_id)
            ->first();

        $tglmasuk = $query->tglmasuk ?? '';

        $hari = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS SUPIR BARU LUAR KOTA')->where('subgrp', 'BATAS SUPIR BARU LUAR KOTA')->first()->text ?? '0';

        return ':attribute' . ' ' . $controller->geterror('BSBLK')->keterangan . 'adalah ' . $hari . ' hari dari tgl masuk supir, tgl masuk supir (' . $tglmasuk . ')';
    }
}
