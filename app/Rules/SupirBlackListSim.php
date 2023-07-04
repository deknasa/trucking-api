<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BlackListSupir;

class SupirBlackListSim implements Rule
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
        $blackListSupir =BlackListSupir::where('nosim',$value)->first();
        $allowed = true;
        if($blackListSupir){
            $allowed = false;
        }
        return $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'sim Supir Sudah Di BlackList';
    }
}
