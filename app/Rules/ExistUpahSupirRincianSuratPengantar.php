<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\UpahSupirRincian;
use Illuminate\Contracts\Validation\Rule;

class ExistUpahSupirRincianSuratPengantar implements Rule
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
        $upahRincian = new UpahSupirRincian();
        $dataUpah = $upahRincian->getValidasiUpahsupir(request()->container_id, request()->statuscontainer_id,$value);
        if($dataUpah == null){
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
