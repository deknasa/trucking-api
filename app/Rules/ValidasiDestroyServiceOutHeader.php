<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\ServiceOutHeaderController;

class ValidasiDestroyServiceOutHeader implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        
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
        $serviceInHeader = app(ServiceOutHeaderController::class);
        $cekdata = $serviceInHeader->cekvalidasi(request()->id);
        
        if($cekdata->original['kodestatus'] =="1"){
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
        return app(ErrorController::class)->geterror('SDC')->keterangan;
    }
}
