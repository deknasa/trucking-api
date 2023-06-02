<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesUangJalanSupirHeader;
use Illuminate\Contracts\Validation\Rule;

class CekMinusSisaPinjamanProsesUangJalan implements Rule
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
        $attribute = substr($attribute,5);
        $nobukti = request()->pengeluarantruckingheader_nobukti[$attribute];
        $prosesUang = new ProsesUangJalanSupirHeader();
        $getSisa = $prosesUang->getSisaPinjamanForValidation($nobukti);
        if($value > $getSisa->sisa){
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
