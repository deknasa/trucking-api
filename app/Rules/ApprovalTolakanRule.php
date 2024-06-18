<?php

namespace App\Rules;

use App\Models\Error;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ApprovalTolakanRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $keterangan;
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
        $queryInvoice = DB::table("invoicedetail")->from(DB::raw("invoicedetail with (readuncommitted)"))
            ->where('orderantrucking_nobukti', $value)
            ->first();

        if ($queryInvoice != '') {

            $error = new Error();
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
            $this->keterangan = 'job trucking <b>' . $value . '</b> ' . $keteranganerror . '. No Bukti invoice <b>' . $queryInvoice->nobukti . '</b>. ' . $keterangantambahanerror;
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
        return $this->keterangan;
    }
}
