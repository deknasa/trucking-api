<?php

namespace App\Http\Requests;

use App\Rules\validasiTglInvoiceEmkl;
use Illuminate\Foundation\Http\FormRequest;

class GetJobInvoiceEmklRequest extends FormRequest
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
        return [            
            'tgldari' => [
                'required', 'date_format:d-m-Y', new validasiTglInvoiceEmkl()
            ],
            'tglsampai' => [
                'required', 'date_format:d-m-Y', new validasiTglInvoiceEmkl()
            ],
        ];
    }
}
