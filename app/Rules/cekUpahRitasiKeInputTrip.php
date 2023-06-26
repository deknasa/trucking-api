<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Ritasi;
use Illuminate\Contracts\Validation\Rule;

class cekUpahRitasiKeInputTrip implements Rule
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
        $attribute = substr($attribute,9);
        $ritasiDari_id = request()->ritasidari_id[$attribute];
        $ritasiKe_id = request()->ritasike_id[$attribute];
        $this->sampai = $value;
        $this->dari = request()->ritasidari[$attribute];
        $ritasi = new Ritasi();
        $cekUpah = $ritasi->cekUpahRitasi($ritasiDari_id, $ritasiKe_id);
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
