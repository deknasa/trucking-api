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


            $triptangki = request()->triptangki_id;
            $getTangki = DB::table("triptangki")->from(DB::raw("triptangki with (readuncommitted)"))
                ->where('id', $triptangki)
                ->first();
            if (!isset($getTangki)) {

                $error = new Error();
                $keteranganerror = $error->cekKeteranganError('DTA') ?? '';
                $keterangantambahanerror = $error->cekKeteranganError('MTB') ?? '';
                $this->keterror = $keteranganerror . ' (' . $keterangantambahanerror . ' ' . request()->triptangki . ')';

                return false;
            } else {
                $upahsupir = DB::table("upahsupirtangkirincian")->from(DB::raw("upahsupirtangkirincian with (readuncommitted)"))->where('upahsupirtangki_id', request()->upah_id)
                    ->where('triptangki_id', $getTangki->id)
                    ->first();
                if (!isset($upahsupir)) {
                    $error = new Error();
                    $keteranganerror = $error->cekKeteranganError('DTA') ?? '';
                    $keterangantambahanerror = $error->cekKeteranganError('MUSTB') ?? '';
                    $this->keterror = $keteranganerror . ' (' . $keterangantambahanerror . ' ' . request()->triptangki . ')';
                    return false;
                } else {
                    if ($upahsupir->nominalsupir == 0) {

                        $error = new Error();
                        $keteranganerror = $error->cekKeteranganError('NUSTBA') ?? '';
                        $this->keterror =  $keteranganerror.' (' . request()->triptangki . ')';
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
