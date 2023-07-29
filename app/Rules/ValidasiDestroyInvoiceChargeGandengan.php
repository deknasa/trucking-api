<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\InvoiceChargeGandenganHeaderController;
use App\Models\InvoiceChargeGandenganHeader;
use Illuminate\Contracts\Validation\Rule;

class ValidasiDestroyInvoiceChargeGandengan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
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
        //
        $controller = new InvoiceChargeGandenganHeaderController;
        $cekdatacetak = $controller->cekvalidasi(request()->id);
        $cekdata = (new InvoiceChargeGandenganHeader())->cekvalidasiaksi(request()->nobukti);
        if ($cekdata['kondisi']) {
            $this->kodeerror = $cekdata['kodeerror'];            
            $this->keterangan = ' ('. $cekdata['keterangan'].')';
            return false;
        }
        $getOriginal = $cekdatacetak->original;
        if ($getOriginal['error'] == true) {
            $this->kodeerror = $getOriginal['kodeerror'];
            $this->keterangan = '';
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
        return app(ErrorController::class)->geterror($this->kodeerror)->keterangan.$this->keterangan;
    }
}
