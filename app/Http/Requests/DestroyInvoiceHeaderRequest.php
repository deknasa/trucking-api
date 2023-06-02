<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\InvoiceHeader;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyInvoiceHeader ;
use App\Http\Controllers\Api\InvoiceHeaderController;


class DestroyInvoiceHeaderRequest extends FormRequest
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
        $controller = new InvoiceHeaderController;
        $invoiceheader = new InvoiceHeader();
        $cekdata = $invoiceheader->cekvalidasiaksi($this->nobukti);
        $cekdatacetak = $controller->cekvalidasi($this->id);
        // dd($cekdata['kondisi']);
        if ($cekdatacetak->original['kodestatus']=='1') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }
        

         
    
        return [
            'id' => [ new ValidasiDestroyInvoiceHeader($cekdata['kondisi'],$cekdtcetak)],
        ];
    }
}
