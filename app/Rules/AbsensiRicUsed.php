<?php

namespace App\Rules;

use App\Models\AbsensiSupirHeader;
use Illuminate\Contracts\Validation\Rule;

class AbsensiRicUsed implements Rule
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
        $date = date('Y-m-d', strtotime($value));
        return AbsensiSupirHeader::isAbsensiRicUsed($date);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Absensi Sudah Ada di RIC';
    }
}
