<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueUpahRitasiSampai implements Rule
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
        $query = DB::table('upahritasi')
            ->from(
                DB::raw("upahritasi as a with (readuncommitted)")
            )
            ->select(
                'a.id'
            )
            ->where('a.kotasampai_id', '=', (request()->kotasampai_id))
            ->where('a.kotadari_id', '=', request()->kotadari_id)
            ->first();



        if (isset($query)) {
            $nilai = false;
        } else {
            $nilai = true;
        }

        return $nilai;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $controller = new ErrorController;
        return 'KOTA SAMPAI ' . $controller->geterror('SPI')->keterangan;
    }
}
