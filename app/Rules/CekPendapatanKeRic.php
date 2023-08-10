<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\GajiSupirHeader;
use Illuminate\Contracts\Validation\Rule;

class CekPendapatanKeRic implements Rule
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
        $tglDari = date('Y-m-d', strtotime(request()->tgldari));
        $tglSampai = date('Y-m-d', strtotime(request()->tglsampai));
        $cek = (new GajiSupirHeader())->cekPendapatanSupir(request()->supir_id, $tglDari, $tglSampai);
        if($cek){
            return true;
        }else{
            return false;
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
        return $controller->geterror('SIK')->keterangan;
    }
}
