<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Models\AbsensiSupirHeader;

class ValidasiDestroyAbsensiSupirHeader implements Rule
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

        $absensisupir = AbsensiSupirHeader::findOrFail(request()->id);

        $isDateAllowed = AbsensiSupirHeader::isDateAllowed($absensisupir->id);
        if (!$isDateAllowed) {
            $this->message = "TEPT";

        }
        $isEditAble = AbsensiSupirHeader::isEditAble($absensisupir->id);
        if (!$isEditAble) {
            $this->message = "BAED";

        }
        $printValidation = AbsensiSupirHeader::printValidation($absensisupir->id);
        if (!$printValidation) {
            $this->message = "SDC";

        }
        $todayValidation = AbsensiSupirHeader::todayValidation($absensisupir->tglbukti);
        if (!$todayValidation) {
            $this->message = "SATL";

        }
        $isApproved = AbsensiSupirHeader::isApproved($absensisupir->nobukti);
        if (!$isApproved) {
            $this->message = "SATL";

        }
        if (($todayValidation && $isApproved) || ($isEditAble && $printValidation) || $isDateAllowed) {
            return true;
        }
        return false;
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
