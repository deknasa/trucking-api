<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesUangJalanSupirDetail;
use Illuminate\Contracts\Validation\Rule;

class CekBankTransferProsesUangJalanEdit implements Rule
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
        $attribute = substr($attribute,16);
        $prosesUangJalanDetail = new ProsesUangJalanSupirDetail();
        $getTransfer = $prosesUangJalanDetail->findTransfer(request()->id);        
        $data = json_decode($getTransfer, true);
        foreach ($data as $item) {
            $status[] = $item['pengeluarantrucking_bank_id'];
        }

        if($status[$attribute] != $value){
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
