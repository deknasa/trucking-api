<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiPenyesuaianUpahSupir implements Rule
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
        if (request()->penyesuaian=='') {
            $penyesuaian = trim(strtoupper(request()->kotasampai));
        } else {
            $penyesuaian = trim(strtoupper(request()->kotasampai)).' - '.trim(strtoupper(request()->penyesuaian));
        }

        $tarif = request()->tarif;
        $status = true;

        if ($tarif != '') {
            if ($penyesuaian != trim(strtoupper($tarif))) {
                $status = false;
            }
        }
        if (request()->tarifmuatan != '') {
            if ($penyesuaian != trim(strtoupper(request()->tarifmuatan))) {
                $status = false;
            }
        }
        if (request()->tarifbongkaran != '') {
            if ($penyesuaian != trim(strtoupper(request()->tarifbongkaran))) {
                $status = false;
            }
        }
        if (request()->tarifexport != '') {
            if ($penyesuaian != trim(strtoupper(request()->tarifexport))) {
                $status = false;
            }
        }
        if (request()->tarifimport != '') {
            if ($penyesuaian != trim(strtoupper(request()->tarifimport))) {
                $status = false;
            }
        }
        return $status;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $controller = new ErrorController;
        return $controller->geterror('PTS')->keterangan;
    }
}
