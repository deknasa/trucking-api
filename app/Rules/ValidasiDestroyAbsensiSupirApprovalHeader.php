<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Models\AbsensiSupirApprovalHeader;

class ValidasiDestroyAbsensiSupirApprovalHeader implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    private $message;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $absensisupir = AbsensiSupirApprovalHeader::findOrFail(request()->id);

        $printValidation = AbsensiSupirApprovalHeader::printValidation(request()->id);
        if (!$printValidation) {
            $this->message = "SDC";
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
        return app(ErrorController::class)->geterror($this->message)->keterangan;
    }
}
