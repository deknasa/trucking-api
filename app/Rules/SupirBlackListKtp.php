<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BlackListSupir;

class SupirBlackListKtp implements Rule
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
        $blackListSupir =BlackListSupir::where('noktp',$value)->first();
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
        return 'Ktp Supir Sudah Di BlackList';
    }
}