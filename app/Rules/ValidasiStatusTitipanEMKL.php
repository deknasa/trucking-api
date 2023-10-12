<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PengeluaranTruckingDetail;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiStatusTitipanEMKL implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
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
        $attribute = substr($attribute,25);
        $suratpengantar_nobukti = (request()->suratpengantar_nobukti[$attribute] == '') ? '' : request()->suratpengantar_nobukti[$attribute];
        if($suratpengantar_nobukti != ''){
            $cekPengeluaran = (new PengeluaranTruckingDetail())->cekTitipanEMKL($value, $suratpengantar_nobukti,$this->id);
            return $cekPengeluaran;
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
        return app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
