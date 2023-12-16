<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiKlaimPosting implements Rule
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
        $getStatus = DB::table("pengeluarantruckingheader")->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))->where('id', request()->id)->first();
        if ($value != $getStatus->statusposting) {
            
            $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('id', '84')->first();
            if($value == $statusPosting->id){
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
        return app(ErrorController::class)->geterror('POST')->keterangan;
    }
}
