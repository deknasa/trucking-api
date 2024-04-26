<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class validasiBankPenerimaanGiro implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $kodeerror;
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
        $bank_id = request()->bank_id;
        $firstValue = strtoupper($bank_id[0]);

        // Iterate through the array starting from the second element
        for ($i = 0; $i < count($bank_id); $i++) {
            if (strtoupper($bank_id[$i]) !== $firstValue) {
                $this->kodeerror = 'TBD';
                return false;
                break; // If a different value is found, exit the loop
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
        return 'bank ' . app(ErrorController::class)->geterror($this->kodeerror)->keterangan;
    }
}
