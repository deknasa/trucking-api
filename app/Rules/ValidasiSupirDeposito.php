<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiSupirDeposito implements Rule
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
        $attribute = substr($attribute, 6);
        $data = request()->supir_id;
        $supir_id = $data[$attribute];
        $dataWithoutNull = array_map(function ($value) {
            return $value !== null ? $value : 'null';
        }, $data);

        // Count the occurrences of each value in the array
        $valueCounts = array_count_values($dataWithoutNull);
        // Check if the value $cek occurs more than once
        if ($valueCounts[$supir_id] > 1) {
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
        return app(ErrorController::class)->geterror('TBS')->keterangan;
    }
}
