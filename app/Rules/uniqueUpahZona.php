<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class uniqueUpahZona implements Rule
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
        $zonadari_id = request()->zonadari_id ?? 0;
        $zonasampai_id = request()->zonasampai_id ?? 0;
        if ($zonadari_id != 0 && $zonasampai_id != 0) {

            $query = DB::table('upahsupir')
                ->from(
                    DB::raw("upahsupir as a with (readuncommitted)")
                )
                ->select(
                    'a.id'
                )
                ->whereRaw("(a.zonadari_id = $zonadari_id and a.zonasampai_id = $zonasampai_id) or ((a.zonadari_id = $zonasampai_id and a.zonasampai_id = $zonadari_id))")
                ->first();

            if ($query != '') {
                return false;
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
        return  $controller->geterror('SPI')->keterangan;
    }
}
