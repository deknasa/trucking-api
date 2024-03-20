<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiApprovalTglBatasLuarKota implements Rule
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
        if ($value != '') {

            $supir = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->select('tglmasuk')
                ->where('id', request()->id)->first();
            $tglbatas = date("Y-m-d", strtotime($value));
            if ($tglbatas < $supir->tglmasuk) {
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
        return 'tgl batas ' . app(ErrorController::class)->geterror('MAX')->keterangan . ' tgl masuk';
    }
}
