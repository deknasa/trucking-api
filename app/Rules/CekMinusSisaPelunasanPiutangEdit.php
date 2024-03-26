<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PelunasanPiutangHeader;
use App\Models\PiutangHeader;
use Illuminate\Contracts\Validation\Rule;

class CekMinusSisaPelunasanPiutangEdit implements Rule
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
        $attribute = substr($attribute, 5);
        $nobukti = request()->piutang_nobukti[$attribute];
        $bayar = (request()->bayar[$attribute] == '') ? 0 : request()->bayar[$attribute];
        $potongan = (request()->potongan[$attribute] == '') ? 0 : request()->potongan[$attribute];
        // 
        $piutang = new PelunasanPiutangHeader();
        $getPiutang = $piutang->getMinusSisaPelunasan($nobukti);
        if ($getPiutang != '') {
            $totalAwal = $getPiutang->nominal - $bayar - $potongan;
            if ($totalAwal < 0) {
                return false;
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
