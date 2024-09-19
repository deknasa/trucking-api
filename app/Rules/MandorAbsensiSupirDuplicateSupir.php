<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MandorAbsensiSupirDuplicateSupir implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($supir_id,$duplicates)
    {
        $this->supir_id = $supir_id;
        $this->duplicates = $duplicates;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */

    private $supir_id;
    private $namasupir;
    private $duplicates;


    public function passes($attribute, $value)
    {
        $this->namasupir = $value;
        if (in_array($this->supir_id,$this->duplicates)){
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
        return $this->namasupir.' Sudah Pernah Di Input';
    }
}
