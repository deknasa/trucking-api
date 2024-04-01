<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\Rule;

class ValidasiKeteranganProsesUangJalan implements Rule
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

        $supir = request()->supir;
        $ketDefault = "DEPOSITO SUPIR " . $supir;
        $data = app(Controller::class)->like_match('%' . strtoupper($ketDefault) . '%', strtoupper($value));
        $this->keterangan = 'KETERANGAN DEPOSITO ' . app(ErrorController::class)->geterror('HMK')->keterangan . ' : <br>' . $ketDefault;
        return $data;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->keterangan;
    }
}
