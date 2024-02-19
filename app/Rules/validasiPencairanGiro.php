<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PencairanGiroPengeluaranHeader;
use Illuminate\Contracts\Validation\Rule;

class validasiPencairanGiro implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $kodeError;
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
        $nobukti = request()->nobukti;
        $nomor = '';
        $exist = 0;
        for ($i = 0; $i < count($nobukti); $i++) {
            if ($nobukti[$i] != '') {
                $cek = (new PencairanGiroPengeluaranHeader())->cekValidasi($nobukti[$i]);
                if($cek['kondisi'])
                {
                    $this->kodeError = $cek['kodeerror'];
                    $exist++;
                    if($nomor== ''){
                        $nomor =$nobukti[$i];
                    } else {
                        $nomor = $nomor.', '. $nobukti[$i];
                    }
                }
            }
        }
        $this->nobukti = $nomor;
        if($exist > 0){
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
        return app(ErrorController::class)->geterror($this->kodeError)->keterangan . ' (' . $this->nobukti.')';
    }
}
