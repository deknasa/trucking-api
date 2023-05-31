<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;

class ExistAbsensiSupirApprovalHeader implements Rule
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
        $absensisupirapprovalheader = DB::table("absensisupirapprovalheader")->from(DB::raw("absensisupirapprovalheader with (readuncommitted)"))
        ->where('id', $value)
        ->first();
    if ($absensisupirapprovalheader == null) {
        return false;
    } else {
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
