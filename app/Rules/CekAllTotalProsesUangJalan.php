<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesUangJalanSupirHeader;
use Illuminate\Contracts\Validation\Rule;

class CekAllTotalProsesUangJalan implements Rule
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
        $nilaiTransfer = array_sum(request()->nilaitransfer);
        $nilaiDeposit = request()->nilaideposit ?? 0;
        $nilaiPinjaman = (request()->pjt_id) ? array_sum(request()->nombayar) : 0;

        $total = $nilaiTransfer - $nilaiDeposit - $nilaiPinjaman;
        $prosesUang = new ProsesUangJalanSupirHeader();
        $getNominal = $prosesUang->getNominalAbsensi(request()->absensisupir);
        if ($getNominal != null) {
            if ($getNominal->nominal != $total) {
                return false;
            } else {
                return true;
            }
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
        return app(ErrorController::class)->geterror('NTC')->keterangan . ' (adjust dengan transfer)';
    }
}
