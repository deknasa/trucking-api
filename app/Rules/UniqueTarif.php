<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;

class UniqueTarif implements Rule
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
                  

        $query = DB::table('tarif')
            ->from(
                DB::raw("tarif as a with (readuncommitted)")
            )
            ->select(
                'a.id'
            )
             ->where('a.tujuan', '=', (request()->tujuan))
            ->first();


         
        if (isset($query))  {
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
        return ':attribute ' . $controller->geterror('SPI')->keterangan;
    }
}
