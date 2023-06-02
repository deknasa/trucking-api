<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesGajiSupirHeader;
use Illuminate\Contracts\Validation\Rule;

class GetPinjPribadiEBS implements Rule
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
        $prosesGajiSupir = new ProsesGajiSupirHeader();
        $getBorongan = $prosesGajiSupir->getSumBoronganForValidation(request()->nobuktiRIC);
        if((float)$getBorongan->pinjamanpribadi != (float)request()->nomPP){
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
        return app(ErrorController::class)->geterror('TVD')->keterangan;
    }
}
