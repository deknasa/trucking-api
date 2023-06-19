<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class JenisRitasiInputTrip implements Rule
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
        $attribute = substr($attribute,15);
        $jenisritasi = request()->jenisritasi[$attribute];
        if($jenisritasi != null && ($value == null || $value == 0)){
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