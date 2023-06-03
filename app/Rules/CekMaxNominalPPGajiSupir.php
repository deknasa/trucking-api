<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\GajiSupirHeader;
use Illuminate\Contracts\Validation\Rule;

class CekMaxNominalPPGajiSupir implements Rule
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
        $attribute = substr($attribute,10);
        $nobukti = request()->pinjPribadi_nobukti[$attribute];
        $potPribadi = new GajiSupirHeader();
        $getPotPribadi = $potPribadi->validasiBayarPotPribadi($nobukti);
        if($value > $getPotPribadi->sisa){
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
        return app(ErrorController::class)->geterror('NTLB')->keterangan.' nominal pinjaman';
    }
}
