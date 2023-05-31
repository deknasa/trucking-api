<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AkunPusatPenerimaanDetail implements Rule
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
        $attribute = substr($attribute, 10);
        $ketCoaKredit = request()->ketcoakredit[$attribute];
        if ($ketCoaKredit != '') {
            $akunPusat = DB::table("akunpusat")->from(DB::raw("akunpusat with (readuncommitted)"))
                ->where('coa', $value)
                ->first();
            if ($akunPusat == null) {
                return false;
            } else {
                return true;
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
        return app(ErrorController::class)->geterror('TVD')->keterangan;
    }
}
