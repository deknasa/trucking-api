<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Tarif;
use Illuminate\Contracts\Validation\Rule;

class ValidasiDestroyTarif implements Rule
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
        $tarif = new Tarif();
        $cekdata = $tarif->cekValidasi(request()->id);
        if($cekdata['kondisi']){
          return false;
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
        return app(ErrorController::class)->geterror('SATL')->keterangan;
    }
}
