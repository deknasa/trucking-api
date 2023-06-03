<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parameter;
use Carbon\Carbon;

class PencairanGiro implements Rule
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
        
        $periode = request()->periode;
        $today = Carbon::now(); 
        $currentMonthYear = $today->format('m-Y'); 
    
        if ($periode > $currentMonthYear) {
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
        return 'format tanggal tidak valid';
    }
}
