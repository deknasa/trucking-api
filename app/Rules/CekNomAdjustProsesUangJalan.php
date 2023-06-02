<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesUangJalanSupirHeader;
use Illuminate\Contracts\Validation\Rule;

class CekNomAdjustProsesUangJalan implements Rule
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
        $prosesUang = new ProsesUangJalanSupirHeader();
        $getNominal = $prosesUang->getNominalAbsensi(request()->absensisupir);
        if ($getNominal != null) {
            if ((float)$getNominal->nominal != (float)$value) {
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
        return  app(ErrorController::class)->geterror('TVD')->keterangan;
    }
}
