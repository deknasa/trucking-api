<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\UpahSupir;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SimpanKandangUpahSupir implements Rule
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
        if($value != null || $value != 0){
            $upahSupir = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', request()->id)->first();
            if($upahSupir->statussimpankandang != null){
                if($value != $upahSupir->statussimpankandang){
                    return false;
                }else{
                    return true;
                }
            }
        }else{
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $controller = new ErrorController;
        return ':attribute' . ' ' . $controller->geterror('TVD')->keterangan;
    }
}
