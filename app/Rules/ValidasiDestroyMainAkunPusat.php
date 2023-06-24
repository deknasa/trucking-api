<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\MainAkunPusat;
use Illuminate\Contracts\Validation\Rule;

class ValidasiDestroyMainAkunPusat implements Rule
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
        $akunPusat = new MainAkunPusat();
        $cekdata = $akunPusat->cekValidasi(request()->id);
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
