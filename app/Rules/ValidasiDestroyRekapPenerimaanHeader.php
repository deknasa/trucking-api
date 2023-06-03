<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidasiDestroyRekapPenerimaanHeader implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($param, $paramcetak)
    {
        
        $this->kondisi = $param;
        $this->kondisicetak = $paramcetak;
    }

    public $kondisi;
    public $kondisicetak;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->kondisi == true) {
            // dd('1');
            return false;
        } else if ($this->kondisicetak == true) {
            // dd('2');
            return false;
        } else {
            // dd('3');
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
        if ($this->kondisi == true) {
            return app(ErrorController::class)->geterror('SATL')->keterangan;
        } else {
            return app(ErrorController::class)->geterror('SDC')->keterangan;
        }
    }
}
