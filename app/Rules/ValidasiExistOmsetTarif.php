<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\TarifRincian;
use Illuminate\Contracts\Validation\Rule;

class ValidasiExistOmsetTarif implements Rule
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
        if (request()->statuslongtrip != '') {
            if (request()->statuslongtrip == 66) {


                $tarifRincian = new TarifRincian();
                $dataTarif = $tarifRincian->getExistNominal(request()->container_id, request()->tarifrincian_id);
                if ($dataTarif == null) {
                    return false;
                } else if ($dataTarif->nominal == 0) {
                    return false;
                } else {
                    return true;
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
        $controller = new ErrorController;
        return $controller->geterror('TBA')->keterangan . ' untuk cont ' . request()->container;
    }
}
