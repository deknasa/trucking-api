<?php

namespace App\Rules;

use App\Models\Error;
use App\Models\AbsensiSupirHeader;
use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\AbsensiSupirHeaderController;

class ValidasiHapusAbsensiAdmin implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    
    public $kodeerror;
    public $keterangan;
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
        $absensisupir = AbsensiSupirHeader::findOrFail(request()->id);
        $isUsedTrip = AbsensiSupirHeader::isUsedTrip(request()->id);
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        if ($isUsedTrip) {
            $keteranganerror = $error->cekKeteranganError('DTSA') ?? '';
            $keterror = 'No Bukti <b>' . $absensisupir->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $this->kodeerror = "DTSA";
            $this->keterangan = $keterror;
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
