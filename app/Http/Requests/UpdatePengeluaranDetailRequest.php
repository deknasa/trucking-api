<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengeluaranDetailRequest extends FormRequest
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
            'tgljatuhtempo.*' => 'required|date_format:d-m-Y',
            'nominal_detail' => 'required|array',
            'nominal_detail.*' => 'required|numeric|gt:0',
            'ketcoadebet' => 'required|array',
            'ketcoadebet.*' => 'required',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required'
        ];
    }
}
