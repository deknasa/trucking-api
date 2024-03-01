<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiApprovalSuratPengantarBiayaTambahan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $nobukti;
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
        $getSP = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"))
            ->select('suratpengantar.nobukti', 'suratpengantar.jobtrucking', 'suratpengantarbiayatambahan.statusapproval')
            ->join(DB::raw("suratpengantar with (readuncommitted)"), 'suratpengantarbiayatambahan.suratpengantar_id', 'suratpengantar.id')
            ->where('suratpengantar.id', $value)
            ->first();

        // cek ric
        $cekGajiSupir = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail with (readuncommitted)"))->where('suratpengantar_nobukti', $getSP->nobukti)->first();
        if ($cekGajiSupir != '') {
            $this->nobukti = $cekGajiSupir->nobukti;
            return false;
        }

        //cek inv
        $cekInv = DB::table("invoicedetail")->from(DB::raw("invoicedetail with (readuncommitted)"))->where('orderantrucking_nobukti', $getSP->jobtrucking)->first();
        if ($cekInv != '') {
            $this->nobukti = $cekInv->nobukti;
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
        return app(ErrorController::class)->geterror('SATL')->keterangan . ' ('.$this->nobukti.')';
    }
}
