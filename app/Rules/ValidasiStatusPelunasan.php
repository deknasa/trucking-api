<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiStatusPelunasan implements Rule
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
        if (request()->statuspelunasan == 483) {
            $notadebet = false;
            $statusnotadebet = false;

            foreach (request()->nominallebihbayar as $value) {
                if ($value > 0) {
                    $notadebet = true;
                    break;
                }
            }
            foreach (request()->statusnotadebet as $value) {
                if ($value > 0) {
                    $statusnotadebet = true;
                    break;
                }
            }
            if($notadebet || $statusnotadebet){
                return false;
            }else{
                return true;
            }
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
        return app(ErrorController::class)->geterror('NDP')->keterangan;
    }
}
