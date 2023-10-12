<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PiutangHeader;
use Illuminate\Contracts\Validation\Rule;

class RequiredCoaPotonganPelunasanPiutang implements Rule
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
        $attribute = substr($attribute,17);
        $potongan = request()->potongan[$attribute];
        $ketPotongan = request()->keteranganpotongan[$attribute];
        if($potongan != 0 || !empty($ketPotongan)){
            if($value == '' || $value == 0) {
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
        return  app(ErrorController::class)->geterror('WI')->keterangan;
    }
}
