<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class HistorySupirMilikMandorValidation implements Rule
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
        $mandor_id = request()->mandor_id;
        $mandorbaru_id = request()->mandorbaru_id;
        if($mandorbaru_id == $mandor_id){
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
        return 'mandor baru dengan lama '.app(ErrorController::class)->geterror('TBS')->keterangan;
    }
}
