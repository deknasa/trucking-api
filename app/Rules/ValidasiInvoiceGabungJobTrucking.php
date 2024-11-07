<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiInvoiceGabungJobTrucking implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $noinvoice;
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
        $nobukti = request()->Id;
        for($i=0; $i<count($nobukti); $i++)
        {
            $bukti = $nobukti[$i];
            $cekParent = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->whereRaw("nobukti='$bukti'")->first();
            if($cekParent->dari_id != 1 && $cekParent->statuslongtrip == 66){
                $cekInvoice = DB::table("invoicedetail")->from(DB::raw("invoicedetail with (readuncommitted)"))->whereRaw("suratpengantar_nobukti LIKE '%$bukti%'")->first();
                if($cekInvoice != '') {
                    $this->noinvoice = $cekInvoice->nobukti;
                    return false;
                } else {
                    $cekInvoice = DB::table("invoicedetail")->from(DB::raw("invoicedetail with (readuncommitted)"))->whereRaw("orderantrucking_nobukti = '$cekParent->jobtrucking'")->first();
                    if($cekInvoice != '') {
                        $this->noinvoice = $cekInvoice->nobukti;
                        return false;
                    }
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
        return 'Job Trucking Sudah di Pakai di Bukti  <b>' . $this->noinvoice . '</b><br>' . app(ErrorController::class)->geterror('SATL')->keterangan;
    }
}
