<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parameter;

class DateTutupBuku implements Rule
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

        $tutupBuku = Parameter::where('grp','TUTUP BUKU')->where('subgrp','TUTUP BUKU')->first();
        $tutupBukuDate = date('Y-m-d', strtotime($tutupBuku->text));
        $allowed = false;
        if($date > $tutupBukuDate){
            $allowed = true;
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
        return 'Tangal tidak bisa diproses sebelum tanggal tutup buku';
    }
}
