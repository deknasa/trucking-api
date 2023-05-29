<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueUpahSupirSampai implements Rule
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
        $query = DB::table('upahsupir')
        ->from(
            DB::raw("upahsupir as a with (readuncommitted)")
        )
        ->select(
            'a.id'
        )
         ->where('a.kotasampai_id', '=', (request()->kotasampai_id))
        ->first();


     
    if (isset($query))  {
        $nilai = false;
    } else {
        $nilai = true;
    }

    return $nilai;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
