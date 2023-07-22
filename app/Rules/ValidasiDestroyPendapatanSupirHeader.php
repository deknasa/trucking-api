<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\PendapatanSupirHeaderController;
use App\Models\Trado;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiDestroyPendapatanSupirHeader implements Rule
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

        $container = app(PendapatanSupirHeaderController::class);
        $cekdata = $container->cekvalidasi(request()->id);
        if($cekdata->original['kodestatus']){
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