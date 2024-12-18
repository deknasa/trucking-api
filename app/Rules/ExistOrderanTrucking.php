<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;

class ExistOrderanTrucking implements Rule
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
        $orderantrucking = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))
            ->where('nobukti', request()->jobtrucking)
            ->first();
        if ($orderantrucking == null) {
            $orderantrucking = DB::table("saldoorderantrucking")->from(DB::raw("saldoorderantrucking with (readuncommitted)"))
                ->where('nobukti', request()->jobtrucking)
                ->first();
            if ($orderantrucking == null) {
                return false;
            } else {
                return true;
            }
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
