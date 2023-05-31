<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class BankPelangganPenerimaanDetail implements Rule
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
        $attribute = substr($attribute, 14);
        $bankpelanggan_id = request()->bankpelanggan_id[$attribute];
        if ($value == null) {
            if ($bankpelanggan_id != null || $bankpelanggan_id != 0) {
                return false;
            } else {
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
        return app(ErrorController::class)->geterror('WI')->keterangan;
    }
}
