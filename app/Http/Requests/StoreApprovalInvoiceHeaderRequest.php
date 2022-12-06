<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalInvoiceHeaderRequest extends FormRequest
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
    Public function rules()
    {
        return [
            'periode' => 'required',
            'approve' => 'required',
            'invoiceId' => 'required',
            'invoice' => 'required'
        ];
    }
    public function attributes()
    {
        return [
            'approve' => 'Proses Data',
            'invoiceId' => 'Pilih Invoice'
        ];
    }
}
