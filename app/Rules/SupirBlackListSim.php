<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BlackListSupir;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;

class SupirBlackListSim implements Rule
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
        $blackListSupir = BlackListSupir::where('nosim', $value)->first();
        $allowed = true;
        if ($blackListSupir != '') {
            $allowed = false;
        } else {
            $status = (new Parameter())->cekId('BLACKLIST SUPIR', 'BLACKLIST SUPIR', 'SUPIR BLACKLIST');
            $cekstatusblacklist = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->where('nosim', $value)->where('statusblacklist', $status)->first();
            if($cekstatusblacklist != ''){
                $allowed = false;
            }
        }

        return $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'sim Supir Sudah Di BlackList';
    }
}
