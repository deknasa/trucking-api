<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;

class ValidasiDestroyJenisTrado implements Rule
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
        if($this->kondisi==true){
            return false;
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
        return app(ErrorController::class)->geterror('SATL')->keterangan;
    }
}
