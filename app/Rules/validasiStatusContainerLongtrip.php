<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class validasiStatusContainerLongtrip implements Rule
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
        if (request()->statuslongtrip == 65) {

            if (request()->statuscontainer_id != '') {
                if (request()->statuscontainer_id == 3) {
                    return false;
                }
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
        return 'TIDAK BOLEH FULL/EMPTY';
    }
}
