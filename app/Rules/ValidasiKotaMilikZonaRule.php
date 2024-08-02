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
        $this->pesan = '';
    }

    public $kotadari;
    public $kotasampai;
    public $pesan;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$this->kotadari || $this->kotasampai) {
            $this->pesan = 'required';
            return false;
        }
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
        if ($this->pesan ="required") {
            return "kota wajib diisi";
        }
        return 'Kota Sudah memiliki zona';
    }
}
