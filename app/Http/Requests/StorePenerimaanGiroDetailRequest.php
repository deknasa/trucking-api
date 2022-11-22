<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanGiroDetailRequest extends FormRequest
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
            'tgljatuhtempo' => 'required|array',
            'tgljatuhtempo.*' => 'required',
            'nominal' => 'required|array',
            'nominal.*' => 'required|numeric|gt:0',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required',
            'bank_id' => 'required|array',
            'bank_id.*' => 'required',
            'bankpelanggan_id' => 'required|array',
            'bankpelanggan_id.*' => 'required',
            'jenisbiaya' => 'required|array',
            'jenisbiaya.*' => 'required'
        ];
    }
}
