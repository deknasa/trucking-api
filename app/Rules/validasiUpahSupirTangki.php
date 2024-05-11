<?php

namespace App\Rules;

use App\Models\Error;
use App\Models\SuratPengantar;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiUpahSupirTangki implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $keterror;
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
        $supir_id = request()->supir_id;
        $trado_id = request()->trado_id;
        if ($supir_id != '' && $trado_id != '') {


            $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
                ->select('a.id')
                ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
                ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
                ->where('a.text', '=', 'TANGKI')
                ->first();
            $getTripTangki = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select('triptangki_id')
                ->where('supir_id', $supir_id)
                ->where('trado_id', $trado_id)
                ->where('tglbukti', date('Y-m-d', strtotime(request()->tglbukti)))
                ->where('statusjeniskendaraan', $jenisTangki->id)
                ->orderBy('id', 'desc')
                ->count();

            if ($getTripTangki > 0) {
                $triptangki = $getTripTangki + 1;
            } else {
                $triptangki = 1;
            }
            $getTangki = DB::table("triptangki")->from(DB::raw("triptangki with (readuncommitted)"))
                ->where('kodetangki', $triptangki)
                ->first();
            if (!isset($getTangki)) {

                $error = new Error();
                $keteranganerror = $error->cekKeteranganError('DTA') ?? '';
                $keterangantambahanerror = $error->cekKeteranganError('MTB') ?? '';
                $this->keterror = $keteranganerror . ' (' . $keterangantambahanerror . ' KE-' . $triptangki . ')';

                return false;
            } else {
                $upahsupir = DB::table("upahsupirtangkirincian")->from(DB::raw("upahsupirtangkirincian with (readuncommitted)"))->where('upahsupirtangki_id', request()->upah_id)
                    ->where('triptangki_id', $getTangki->id)
                    ->first();
                if (!isset($upahsupir)) {
                    $error = new Error();
                    $keteranganerror = $error->cekKeteranganError('DTA') ?? '';
                    $keterangantambahanerror = $error->cekKeteranganError('MUSTB') ?? '';
                    $this->keterror = $keteranganerror . ' (' . $keterangantambahanerror . ' KE-' . $triptangki . ')';
                    return false;
                } else {
                    if ($upahsupir->nominalsupir == 0) {

                        $error = new Error();
                        $keteranganerror = $error->cekKeteranganError('NUSTBA') ?? '';
                        $this->keterror =  $keteranganerror;
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
        return $this->keterror;
    }
}
