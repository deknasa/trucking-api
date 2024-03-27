<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;
use App\Models\HutangHeader;


class HutangBayarLimit implements Rule
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
        $attribute = substr($attribute, 6);
        $hutang_nobukti = request()->hutang_nobukti[$attribute];
        $hutang = HutangHeader::where('nobukti', $hutang_nobukti)->first();
        if ($hutang != '') {

            if (request()->bayar[$attribute] > $hutang->total) {
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
        $controller = new ErrorController;
        return ':attribute' . ' ' . $controller->geterror('NBH')->keterangan;
    }
}
