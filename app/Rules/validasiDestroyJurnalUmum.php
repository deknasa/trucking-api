<?php

namespace App\Rules;

use App\Models\Error;
use App\Models\Locking;
use App\Models\Parameter;
use DateTime;
use Illuminate\Contracts\Validation\Rule;

class validasiDestroyJurnalUmum implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $message;
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

        $nameUser = auth('api')->user()->name;

        $getEditing = (new Locking())->getEditing('jurnalumumheader', $value);
        $useredit = $getEditing->editing_by ?? '';
        if ($useredit != '' && $useredit != $nameUser) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('PENGELUARAN KAS/BANK BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes < $waktu) {

                $keteranganerror = (new Error())->cekKeteranganError('SDE') ?? '';
                $keterangantambahanerror = (new Error())->cekKeteranganError('PTBL') ?? '';
                $keterror = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $this->message = $keterror;


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
        return $this->message;
    }
}
