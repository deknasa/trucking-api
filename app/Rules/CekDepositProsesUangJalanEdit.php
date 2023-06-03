<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesUangJalanSupirDetail;
use Illuminate\Contracts\Validation\Rule;

class CekDepositProsesUangJalanEdit implements Rule
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
        $attribute = substr($attribute,13);
        $prosesUangJalanDetail = new ProsesUangJalanSupirDetail();
        $getTransfer = $prosesUangJalanDetail->deposito(request()->id);      
        if((float)$getTransfer->nilaideposit != (float)$value){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror('TVD')->keterangan;
    }
}
