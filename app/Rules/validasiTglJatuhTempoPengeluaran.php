<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTglJatuhTempoPengeluaran implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $koderror;
    public $tambahan;
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
        $alatbayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('id', request()->alatbayar_id)->first();
        if ($alatbayar != '') {

            if ($alatbayar->namaalatbayar == 'GIRO') {
                $tglJatuhTempo = request()->tgljatuhtempo;
                $tglBukti = request()->tglbukti;
                $firstValue = $tglJatuhTempo[0];

                // Iterate through the array starting from the second element
                for ($i = 0; $i < count($tglJatuhTempo); $i++) {
                    if ($tglJatuhTempo[$i] < $tglBukti) {
                        $this->koderror = 'HDSD';
                        $this->tambahan = 'tgl bukti';
                        return false;
                        break; // If a different value is found, exit the loop
                    }
                    if ($tglJatuhTempo[$i] !== $firstValue) {
                        $this->koderror = 'TBD';
                        return false;
                        break; // If a different value is found, exit the loop
                    }
                }
            } else {
                $tglJatuhTempo = request()->tgljatuhtempo;
                $firstValue = request()->tglbukti;

                // Iterate through the array starting from the second element
                for ($i = 0; $i < count($tglJatuhTempo); $i++) {
                    if ($tglJatuhTempo[$i] !== $firstValue) {
                        $this->koderror = 'TBD';
                        return false;
                        break; // If a different value is found, exit the loop
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
        return 'tgl jatuh tempo ' . app(ErrorController::class)->geterror($this->koderror)->keterangan . ' ' . $this->tambahan;
    }
}
