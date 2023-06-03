<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesUangJalanSupirDetail;
use Illuminate\Contracts\Validation\Rule;

class CekBankPengembalianProsesUangJalanEdit implements Rule
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
        $attribute = substr($attribute,19);
        $prosesUangJalanDetail = new ProsesUangJalanSupirDetail();
        $getTransfer = $prosesUangJalanDetail->pengembalian(request()->id);      
        if($getTransfer->bank_idpengembalian != $value){
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
        return app(ErrorController::class)->geterror('HPDL')->keterangan;
    }
}
