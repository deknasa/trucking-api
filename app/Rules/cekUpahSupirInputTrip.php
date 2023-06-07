<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\UpahSupir;
use Illuminate\Contracts\Validation\Rule;

class cekUpahSupirInputTrip implements Rule
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
        $upahsupir = new UpahSupir();
        $this->dari = request()->dari;
        $this->sampai = request()->sampai;
        $this->container = request()->container;
        $this->statuscontainer = request()->statuscontainer;
        $cekUpah = $upahsupir->validasiUpahSupirInputTrip(request()->dari_id, request()->sampai_id, request()->container_id, request()->statuscontainer_id);
        if($cekUpah == null){
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
        return app(ErrorController::class)->geterror('USBA')->keterangan." dari $this->dari KE $this->sampai cont $this->container $this->statuscontainer";
    }
}
