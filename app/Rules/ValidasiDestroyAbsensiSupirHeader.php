<?php

namespace App\Rules;

use App\Models\Error;
use App\Models\AbsensiSupirHeader;
use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\AbsensiSupirHeaderController;

class ValidasiDestroyAbsensiSupirHeader implements Rule
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

        // $isDateAllowed = AbsensiSupirHeader::isDateAllowed($absensisupir->id);
        // if (!$isDateAllowed) {
        //     $this->message = "TEPT";

        // }
        // $isEditAble = AbsensiSupirHeader::isEditAble($absensisupir->id);
        // if (!$isEditAble) {
        //     $this->message = "BAED";

        // }
        // $printValidation = AbsensiSupirHeader::printValidation($absensisupir->id);
        // if (!$printValidation) {
        //     $this->message = "SDC";

        // }
        // $todayValidation = AbsensiSupirHeader::todayValidation($absensisupir->tglbukti);
        // if (!$todayValidation) {
        //     $this->message = "SATL";

        // }
        // $isApproved = AbsensiSupirHeader::isApproved($absensisupir->nobukti);
        // if (!$isApproved) {
        //     $this->message = "SATL";

        // }
        // if (($todayValidation && $isApproved) || ($isEditAble && $printValidation) || $isDateAllowed) {
        //     return true;
        // }
        // return false;
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

        $cekCetak = app(AbsensiSupirHeaderController::class)->cekvalidasi(request()->id);
        $getOriginal = $cekCetak->original;
        if ($getOriginal['error'] == true) {
            $this->kodeerror = $getOriginal['kodeerror'];
            $this->keterangan = $getOriginal['message'];
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
