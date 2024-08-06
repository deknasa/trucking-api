<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiKlaimPengeluaranStok implements Rule
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
        if (request()->statustanpabukti == 3) {//jika approval tidak perlu validasi
            return true;
        }
        $attribute = substr($attribute,24);
        $penerimaanStok = request()->penerimaanstok_nobukti[$attribute]?? '';
        if($value == ''){
            if($penerimaanStok == ''){
                return false;
            }else{
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
        return app(ErrorController::class)->geterror('WI')->keterangan;
    }
}
