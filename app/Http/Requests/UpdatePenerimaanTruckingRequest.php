<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenerimaanTruckingRequest extends FormRequest
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
            'kodepenerimaan' => 'required',
            'keterangan' => 'required',
            'coa' => 'required',
            'format' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'kodepenerimaan' => 'kode penerimaan',
            'keterangan' => 'keterangan',
            'coa' => 'coa',
            'format' => 'format bukti',
        ];
    }
}
