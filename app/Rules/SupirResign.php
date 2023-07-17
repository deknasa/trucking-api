<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Supir;
use Illuminate\Contracts\Validation\Rule;

class SupirResign implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->kondisi = $param;
    }
    public $kondisi;

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
            return true;
        } else {
            return false;
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
        return ':attribute' . ' ' . $controller->geterror('SPI')->keterangan;
    }
}
