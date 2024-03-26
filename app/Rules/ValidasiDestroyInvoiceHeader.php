<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\InvoiceHeaderController;
use App\Models\InvoiceHeader;

class ValidasiDestroyInvoiceHeader implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public $kodeerror;
    public $keterangan;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $controller = new InvoiceHeaderController;
        $invoiceheader = new InvoiceHeader();
        $cekdata = $invoiceheader->cekvalidasiaksi(request()->nobukti);
        $cekdatacetak = $controller->cekvalidasi(request()->id);

        if ($cekdata['kondisi']) {
            $this->kodeerror = $cekdata['kodeerror'];            
            $this->keterangan = $cekdata['keterangan'] ;
            return false;
        }
        $getOriginal = $cekdatacetak->original;
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
