<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ExistAgen implements Rule
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
        $agen = DB::table("agen")->from(DB::raw("agen with (readuncommitted)"))
            ->where('id', request()->agen_id)
            ->first();
        if($agen == null){
            return false;
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
