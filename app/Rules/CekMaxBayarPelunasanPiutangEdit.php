<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PelunasanPiutangHeader;
use App\Models\PiutangHeader;
use Illuminate\Contracts\Validation\Rule;

class CekMaxBayarPelunasanPiutangEdit implements Rule
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
        $piutang = new PelunasanPiutangHeader();
        // 
        $getPiutang = $piutang->getEditPelunasan($nobukti, request()->agen_id);
        if ($getPiutang != '') {
            $totalAwal = $getPiutang->sisa + $value + $potongan;
            if ((float)$total > (float)$totalAwal) {
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
