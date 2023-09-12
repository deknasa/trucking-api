<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiStatusNotaKredit implements Rule
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
        if (isset(request()->statusnotakredit)) {
            if (count(request()->statusnotakredit) > 0) {
                $statusNotaKredit = request()->statusnotakredit;
                $firstValue = null; // Initialize the first value as null

                // Find the first non-zero value in the array
                foreach ($statusNotaKredit as $value) {
                    if ($value != 0) {
                        $firstValue = $value;
                        break;
                    }
                }

                for($i=0; $i < count(request()->statusnotakredit); $i++){
                    if($statusNotaKredit[$i] != 0)
                    {
                        if ($statusNotaKredit[$i] != $firstValue) {
                            return false;
                        }
                    }
                    
                }
                return true;
            }
            return true;
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
        return 'TIPE NOTA KREDIT ' . app(ErrorController::class)->geterror('TBD')->keterangan;
    }
}
