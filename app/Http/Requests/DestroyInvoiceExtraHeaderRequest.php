<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\InvoiceExtraHeader;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyInvoiceExtraHeader ;
use App\Http\Controllers\Api\InvoiceExtraHeaderController;

class DestroyInvoiceExtraHeaderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $controller = new InvoiceExtraHeaderController;
        $invoiceextraheader = new InvoiceExtraHeader();
        $cekdata = $invoiceextraheader->cekvalidasiaksi($this->nobukti);
        $cekdatacetak = $controller->cekvalidasi($this->id);
        // dd($cekdata['kondisi']);
        if ($cekdatacetak->original['kodestatus']=='1') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }
        

         
    
        return [
            'id' => [ new ValidasiDestroyInvoiceExtraHeader($cekdata['kondisi'],$cekdtcetak)],
        ];
    }
}
