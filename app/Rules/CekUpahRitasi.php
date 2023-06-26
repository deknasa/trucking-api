<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Ritasi;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CekUpahRitasi implements Rule
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
        $this->dari = request()->dari;
        $this->sampai = request()->sampai;
        $ritasi = new Ritasi();
        $cekUpah = $ritasi->cekUpahRitasi(request()->dari_id, request()->sampai_id);
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
        return app(ErrorController::class)->geterror('URBA')->keterangan." dari $this->dari KE $this->sampai";
    }
}
