<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\AlatBayar;
use App\Http\Controllers\Api\ErrorController;

class ValidasiDestroyAlatBayar implements Rule
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
        $alatBayar = new AlatBayar();
        $cekdata = $alatBayar->cekvalidasihapus(request()->id);
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
