<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiKtpPemutihan implements Rule
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
        $ktp = request()->noktp;
        $pemutihan = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
        ->select(DB::raw("supir.noktp"))
        ->where('supir.noktp', $ktp)
        ->where('supir.statusaktif', 1)
        ->first();

        if($pemutihan != ''){
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
        return app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
