<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\TarifRincian;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class ExistTarifRincianSuratPengantar implements Rule
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
        $tarifRincian = new TarifRincian();
        $dataTarif = $tarifRincian->getValidasiTarif(request()->container_id, request()->upah_id);
        if($dataTarif == null){
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
        $controller = new ErrorController;
        return ':attribute' . ' ' . $controller->geterror('TVD')->keterangan;
    }
}
