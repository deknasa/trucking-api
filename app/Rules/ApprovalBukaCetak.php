<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parameter;
use Carbon\Carbon;

class ApprovalBukaCetak implements Rule
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

        $tutupBuku = Parameter::where('grp','TUTUP BUKU')->where('subgrp','TUTUP BUKU')->first();
        $tutupBukuDate = date('m-Y', strtotime($tutupBuku->text));
        $allowed = false;
        if($value > $tutupBukuDate){
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
        return 'Tanggal tidak bisa diproses sebelum tanggal tutup buku';
    }
}
