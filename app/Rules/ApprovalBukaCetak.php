<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parameter;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

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
        $table = request()->table;
        if($table == 'PEMUTIHANSUPIR'){
            $table = 'PEMUTIHANSUPIRHEADER';
        }
        $allowed = false;
        $tutupBuku = Parameter::where('grp', 'TUTUP BUKU')->where('subgrp', 'TUTUP BUKU')->first();
        $tutupBukuDate = date('Y-m-d', strtotime($tutupBuku->text));

        foreach ($value as $val) {
            $getTgl = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('tglbukti')->where('id', $val)->first();
            $date = date('Y-m-d', strtotime($getTgl->tglbukti));

            if ($date > $tutupBukuDate) {
                $allowed = true;
            }
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
