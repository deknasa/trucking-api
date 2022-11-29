<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenerimaanGiroDetailRequest extends FormRequest
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
            'bank' => 'required|array',
            'bank.*' => 'required',
            'bankpelanggan' => 'required|array',
            'bankpelanggan.*' => 'required',
            'jenisbiaya' => 'required|array',
            'jenisbiaya.*' => 'required'
        ];
    }
}
