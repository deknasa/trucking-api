<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Error;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiBatasLuarKota implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $keterror;
    public $errorid;
    public $hari;
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
        $idfullempty = DB::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text as id'
            )
            ->where('grp', 'STATUS CONTAINER')
            ->where('subgrp', 'STATUS CONTAINER FULL EMPTY')
            ->first()->id ?? 0;

        $upahsupir = db::table("upahsupir")->from(db::raw("upahsupir with (readuncommitted)"))->where('id', request()->upah_id)->first();

        if (!isset($upahsupir)) {
            $this->errorid = 3;
            return false;
        }


        $batasluarkota = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS KM LUAR KOTA')->where('subgrp', 'BATAS KM LUAR KOTA')->first()->text ?? '0';

        if (request()->statuscontainer_id == $idfullempty) {
            $jarak = $upahsupir->jarakfullempty;
            $jarakbatasluarkota = $batasluarkota * 2;
        } else {
            $jarak = $upahsupir->jarak;
            $jarakbatasluarkota = $batasluarkota;
        }

        $supir = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->where('id', request()->supir_id)->first();
        if (isset($supir)) {
            $statusluarkota = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LUAR KOTA')->where('text', 'BOLEH LUAR KOTA')->first()->id ?? '0';

            if ($jarak > $jarakbatasluarkota) {

                if ($supir->statusluarkota != $statusluarkota) {
                    $this->errorid = 2;
                    return false;
                } else {
                    if (date('Y-m-d', strtotime(request()->tglbukti)) <= $supir->tglbatastidakbolehluarkota) {
                        $this->hari = date('d-m-Y', strtotime("+1 days", strtotime($supir->tglbatastidakbolehluarkota)));
                        $this->errorid = 1;
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $controller = new ErrorController;
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        if ($this->errorid == 2) {
            return ':attribute' . ' ' . $controller->geterror('STLK')->keterangan . ' <br> ' . $keterangantambahanerror;
        } elseif ($this->errorid == 1) {
            return $controller->geterror('BSBLK')->keterangan . ' adalah <b>' . $this->hari . '</b> <br> ' . $keterangantambahanerror;;
        } else {
            return  $controller->geterror('DBL')->keterangan . ' <br> ' . $keterangantambahanerror;
        }
    }
}
