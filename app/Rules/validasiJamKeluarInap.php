<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class validasiJamKeluarInap implements Rule
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
        $tglKeluar = date('Y-m-d', strtotime(request()->jamkeluarinap));
        $tglMasuk = date('Y-m-d', strtotime(request()->jammasukinap));
        if(request()->jamkeluarinap != '' && request()->jammasukinap != ''){
            if($tglKeluar <= $tglMasuk){
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
        return app(ErrorController::class)->geterror('MAX')->keterangan . ' Tanggal & Jam Masuk';
    }
}
