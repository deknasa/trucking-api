<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiSupirSerap implements Rule
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
        $supir_id = request()->supir_id;
        $supirserap_id = request()->supirserap_id;
        if($supirserap_id == $supir_id){
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
        return 'supir '.app(ErrorController::class)->geterror('TBS')->keterangan;
    }
}
