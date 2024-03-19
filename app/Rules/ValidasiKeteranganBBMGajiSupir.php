<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\Rule;

class ValidasiKeteranganBBMGajiSupir implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $keterangan;
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
        $ketDefault = "HUTANG BBM SUPIR " . request()->supir . " PERIODE " . request()->tgldari . " S/D " . request()->tglsampai;
        $this->keterangan = $ketDefault;
        $data=app(Controller::class)->like_match('%'.$ketDefault.'%', strtoupper($value));

        return $data;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'KETERANGAN BBM ' . app(ErrorController::class)->geterror('HMK')->keterangan. ' : <br>'. $this->keterangan;
    }
}
