<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class validasiTarikDeposito implements Rule
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
        $supirheader_id = request()->supirheader_id ?? 0;
        if ($supirheader_id != 0) {
            $supir_ids = request()->supir_id;
            $hasDifferentValue = false;

            if (isset($supir_ids)) {

                foreach ($supir_ids as $supir_id) {
                    if ($supir_id != $supirheader_id) {
                        $hasDifferentValue = true;
                        return false;
                    }
                }
            }
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
        return 'DATA SUPIR DEPOSITO ' . app(ErrorController::class)->geterror('TSD')->keterangan . ' SUPIR TERPILIH';
    }
}
