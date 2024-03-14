<?php

namespace App\Rules;

use App\Models\Error;
use App\Models\TripInap;
use Illuminate\Contracts\Validation\Rule;

class validasiApprovalTripInap implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $data;
    public $keterangan;
    public $keterangantambahanerror;
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
        $exist = 0;
        $ket = '';
        for ($i = 0; $i < count(request()->Id); $i++) {
            $tripExist = TripInap::find(request()->Id[$i]);
            if ($tripExist == '') {
                $trado = request()->trado[$i];
                $supir = request()->supir[$i];
                $absen = request()->absen[$i];

                $error = new Error();
                $this->keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
                $this->keterangan = $error->cekKeteranganError('DTA') ?? '';
                $ket .= 'Absensi <b>' . $absen . '</b> trado <b>' . $trado . '</b> supir <b>' . $supir . '</b><br>';
                $exist++;
            }
        }
        if($exist > 0){
            $this->data = $ket;
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
        return $this->data. $this->keterangan. ' <br> ' . $this->keterangantambahanerror;
    }
}
