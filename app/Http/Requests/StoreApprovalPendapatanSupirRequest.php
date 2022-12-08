<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalPendapatanSupirRequest extends FormRequest
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
            'periode' => 'required',
            'approve' => 'required',
            'pendapatanId' => 'required'
        ];

    }

    public function attributes()
    {
        return [
            'approve' => 'Proses Data',
            'pendapatanId' => 'Pilih no bukti pendapatan supir'
        ];
    }
}
