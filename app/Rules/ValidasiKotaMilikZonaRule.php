<?php

namespace App\Rules;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;

class ValidasiKotaMilikZonaRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($kotadari,$kotasampai)
    {
        $this->kotadari = $kotadari;
        $this->kotasampai = $kotasampai;
    }

    public $kotadari;
    public $kotasampai;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $kotadari = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))->where('id',$this->kotadari)->first();
        $kotasampai = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))->where('id',$this->kotasampai)->first();

        if (($kotadari->zona_id) && ($kotasampai->zona_id)) {
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
        return 'Kota Sudah memiliki zona';
    }
}
