<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class validasiJamMasukInap implements Rule
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
        $tglabsensi = date('Y-m-d', strtotime(request()->tglabsensi));
        $tglMasuk = date('Y-m-d', strtotime(request()->jammasukinap));
        if(request()->tglabsensi != '' && request()->jammasukinap != ''){
            if($tglMasuk != $tglabsensi){
                return false;
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
        return app(ErrorController::class)->geterror('HSD')->keterangan . ' TGL ABSENSI';
    }
}
