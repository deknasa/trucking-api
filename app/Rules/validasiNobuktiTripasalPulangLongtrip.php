<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiNobuktiTripasalPulangLongtrip implements Rule
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
        if (request()->statuslongtrip == 66 && request()->sampai_id == 1) {
            if (request()->jobtrucking != '') {
                $cekQuery = DB::table("suratpengantar")->from(db::raw("suratpengantar with (readuncommitted)"))
                    ->where('jobtrucking', request()->jobtrucking)
                    ->where('id', '!=', request()->id)
                    ->where('dari_id', 1)
                    ->first();
                if($cekQuery == ''){
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
        return app(ErrorController::class)->geterror('WI')->keterangan;
    }
}
