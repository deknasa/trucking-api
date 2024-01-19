<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiSuratPengantarTripInap implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $trado;
    public $error;
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
        $this->trado = request()->trado;
        $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->where('nobukti', $value)
            ->first();

        $trado_id = request()->trado_id;
        $supir_id = request()->supir_id;
        if ($query->trado_id != $trado_id && $query->supir_id != $supir_id) {
            $this->error = 'BUKAN TRIP MILIK ' . $this->trado;
            return false;
        }
        $now = date('Y-m-d');
        $cektripinap = DB::table("tripinap")->from(DB::raw("tripinap with (readuncommitted)"))
            ->whereRaw("CONVERT(VARCHAR(10), created_at, 23) = '$now'")
            ->where('suratpengantar_nobukti', $value);
        if (request()->id != '') {
            $cektripinap->where('id', '<>', request()->id);
        }
        $result = $cektripinap->first();
        if ($result != '') {

            $this->error = 'TRIP SUDAH PERNAH DIINPUT HARI INI';
            return false;
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
        return $this->error;
    }
}
