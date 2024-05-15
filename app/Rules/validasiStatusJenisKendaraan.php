<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiStatusJenisKendaraan implements Rule
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
        $trip = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('statusjeniskendaraan')->where('id', request()->id)->first();
        if($trip != ''){
            if($trip->statusjeniskendaraan != request()->statusjeniskendaraan){
                return false;
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
        return app(ErrorController::class)->geterror('JKTB')->keterangan;
    }
}
