<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Ritasi;
use App\Models\Trado;
use Illuminate\Contracts\Validation\Rule;

class ExistTradoRitasi implements Rule
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
        if (request()->suratpengantar_nobukti != null) {

            $ritasi = new Ritasi();
            $get = $ritasi->ExistTradoSupirRitasi(request()->suratpengantar_nobukti);
            if ($get->trado_id == $value) {
                return true;
            } else {
                return false;
            }
        } else {
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
        return ':attribute' . ' ' . $controller->geterror('TVD')->keterangan.'. trado tidak sama dengan trado di trip';
    }
}
