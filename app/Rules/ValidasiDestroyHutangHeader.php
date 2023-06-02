<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;

class ValidasiDestroyHutangHeader implements Rule
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
        // dd($this->kondisicetak);
        if ($this->kondisi == true) {
            return false;
        } else if ($this->kondisicetak == true) {
            return false;
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
        return app(ErrorController::class)->geterror('SATL')->keterangan;
    }
}
