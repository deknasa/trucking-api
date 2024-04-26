<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class validasiNoWarkatPenerimaanGiro implements Rule
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
        $nowarkat = request()->nowarkat;
        $firstValue = strtoupper($nowarkat[0]);

        // Iterate through the array starting from the second element
        for ($i = 0; $i < count($nowarkat); $i++) {
            if (strtoupper($nowarkat[$i]) !== $firstValue) {
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
        return 'no warkat ' . app(ErrorController::class)->geterror($this->kodeerror)->keterangan;
    }
}
