<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Contracts\Validation\Rule;

class validasiKomisiSupirUpahSupir implements Rule
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
        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
        $attribute = substr($attribute, 14);
        $nominalsupir = (request()->nominalsupir[$attribute] == '') ? 0 : request()->nominalsupir[$attribute];
        if($cabang == 'MEDAN'){
            if($nominalsupir > 0){
                if($value <= 0){
                    return false;
                }
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
        return app(ErrorController::class)->geterror('WI')->keterangan;
    }
}
