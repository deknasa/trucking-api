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

        $printValidation = (new AbsensiSupirApprovalHeader())->printValidation(request()->id);
        if (!$printValidation) {
            $this->kodeerror = "SDC";
            $this->keterangan = '';
            return false;
        }
        $cekdata = (new AbsensiSupirApprovalHeader())->cekvalidasiaksi(request()->id);
        if ($cekdata['kondisi']) {
            $this->kodeerror = $cekdata['kodeerror'];
            $this->keterangan = ' ('. $cekdata['keterangan'].')';
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
        return app(ErrorController::class)->geterror($this->kodeerror)->keterangan.$this->keterangan;
    }
}
