<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiGajiKenekSP implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $gaji;
    public function __construct($gaji)
    {
        $this->gaji = $gaji;
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
        if (request()->statuscontainer_id != '' && request()->container_id != '' && request()->upah_id != '') {
            $cekGajiKenek = DB::table("upahsupirrrincian")->from(DB::raw("upahsupirrincian with (readuncommitted)"))
                ->where('upahsupir_id', request()->upah_id)
                ->where('statuscontainer_id', request()->statuscontainer_id)
                ->where('container_id', request()->container_id)
                ->first();

            if ($this->gaji == 'gajikenek') {

                if ($value > $cekGajiKenek->nominalkenek) {
                    return false;
                }
            }

            
            if ($this->gaji == 'gajisupir') {

                if ($value > $cekGajiKenek->nominalsupir) {
                    return false;
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
        return 'nominal ' . app(ErrorController::class)->geterror('HBSD')->keterangan . ' data di master';
    }
}
