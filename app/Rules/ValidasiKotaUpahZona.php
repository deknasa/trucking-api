<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiKotaUpahZona implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $statusZonaId;
    public function __construct($statusZonaId)
    {
        $this->statusZonaId = $statusZonaId;
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
        if(request()->statusupahzona != $this->statusZonaId && $value!=''){
            return false;
        }else{
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
        return app(ErrorController::class)->geterror('TSF')->keterangan;
    }
}
