<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesGajiSupirHeader;
use Illuminate\Contracts\Validation\Rule;

class RuleBankBbmEBSEdit implements Rule
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
        $prosesGaji = new ProsesGajiSupirHeader();
        $get = $prosesGaji->showBBM(request()->id);
        if($get != null){
            if($value != $get['bank_idBBM']){
                return false;
            }else{
                return true;
            }
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
