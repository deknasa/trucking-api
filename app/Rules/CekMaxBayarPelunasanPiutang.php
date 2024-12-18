<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PiutangHeader;
use Illuminate\Contracts\Validation\Rule;

class CekMaxBayarPelunasanPiutang implements Rule
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
        $nobukti = request()->piutang_nobukti[$attribute];
        $potongan = (request()->potongan[$attribute] == '') ? 0 : request()->potongan[$attribute];
        $total = $potongan + $value;
        $piutang = new PiutangHeader();
        if (request()->agen_id != '' || request()->agen_id != 0) {
            $getPiutang = $piutang->getSisaPiutang($nobukti, request()->agen_id);
            if ($getPiutang != '') {
                if ($total > $getPiutang->sisa) {
                    return false;
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
        return  app(ErrorController::class)->geterror('NTLB')->keterangan . ' nominal piutang';
    }
}
