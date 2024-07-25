<?php

namespace App\Rules;

use App\Models\Error;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTripBiayaExtraSupir implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $ket;
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
        $getJobTrucking = db::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('nobukti', $value)->first();
        if (isset($getJobTrucking)) {
            $getInvoice = db::table("invoicedetail")->from(db::raw("invoicedetail with (readuncommitted)"))->where('orderantrucking_nobukti', $getJobTrucking->jobtrucking)->first();

            if (isset($getInvoice)) {
                $error = new Error();
                $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? ''; $keteranganerror = $error->cekKeteranganError('SATL2');
                $this->ket = 'Trip <b>' . $value . '</b> ' . $keteranganerror . '. No Bukti invoice <b>' . $getInvoice->nobukti . '.</b> ' . $keterangantambahanerror;
                return false;
            }
        } else {
            $error = new Error();
            $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? ''; $keteranganerror = $error->cekKeteranganError('DTA');
            $this->ket = 'Trip <b>' . $value . '</b>' . $keteranganerror . '. ' . $keterangantambahanerror;
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
        return $this->ket;
    }
}
