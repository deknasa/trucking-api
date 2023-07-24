<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\InvoiceExtraHeaderController;
use App\Models\InvoiceExtraHeader;

class ValidasiDestroyInvoiceExtraHeader implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public $kondisi;
    public $kondisicetak;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        
        $controller = new InvoiceExtraHeaderController;
        $invoiceextraheader = new InvoiceExtraHeader();
        $cekdata = $invoiceextraheader->cekvalidasiaksi(request()->nobukti);
        $cekdatacetak = $controller->cekvalidasi(request()->id);
        
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
