<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiKeteranganLuarKota implements Rule
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
        $cekParameter = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LUAR KOTA')->where('text', 'BOLEH LUAR KOTA')->first()->id;
        $status = request()->statusluarkota;
        if ($status != '') {
            if ($status == $cekParameter) {
                if (request()->tglbatas != '') {
                    if ($value == '') {
                        return false;
                    }
                }
            } else {
                if ($value == '') {
                    return false;
                }
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
        return 'keterangan ' . app(ErrorController::class)->geterror('WI')->keterangan;
    }
}
