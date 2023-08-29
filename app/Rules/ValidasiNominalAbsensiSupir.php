<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiNominalAbsensiSupir implements Rule
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
        $nominal=db::table("absensisupirdetail")->from(db::raw("absensisupirdetail a with (readuncommitted)"))
        ->select(
            db::raw("sum(uangjalan) as nominal")
        )->where('nobukti',request()->absensisupir_nobukti)
        ->first()->nominal;

        $allowed = false;
        if($nominal > 0){
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
        return 'Total Nominal Absensi 0, Proses tidak bisa dilanjutkan';
    }
}
