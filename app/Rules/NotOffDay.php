<?php

namespace App\Rules;

use App\Models\HariLibur;
use Illuminate\Contracts\Validation\Rule;

class NotOffDay implements Rule
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
        
        $offDay = HariLibur::where('tgl', '=', $date)->first();

        return !$offDay;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Tidak dapat memilih hari libur.';
    }
}
