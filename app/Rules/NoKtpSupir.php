<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Supir;
use Illuminate\Contracts\Validation\Rule;

class NoKtpSupir implements Rule
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
        $noktp = request()->noktp;
        $dataSupir = (new Supir())->validationSupirResign($noktp);
       
        if($dataSupir != null){
            return true;
        }else{
            $dataBolehInput = (new Supir())->validasiBolehInput($noktp);
            if($dataBolehInput != null){
                return true;
            }
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
        return ':attribute' . ' ' . $controller->geterror('SPI')->keterangan;
    }
}
