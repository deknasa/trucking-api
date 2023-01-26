<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotInKarakter_ implements Rule
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

        
        $y=strlen($value);
        $notin=true;
        for ($x = 0; $x <= $y; $x++) {
            $ambilkarakter=substr($value,$x,1);
            if ($ambilkarakter=='_') {
                $notin=false;
                break;
            }
            
          }
          $value=$notin;

        return $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Format :attribute Salah';
    }
}
