<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class CoaKreditPenerimaanDetail implements Rule
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
        $attribute = substr($attribute,10);
        $ketCoaKredit = request()->ketcoakredit[$attribute];

        if($ketCoaKredit != null && $value == null){
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
