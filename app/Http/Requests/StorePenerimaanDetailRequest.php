<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanDetailRequest extends FormRequest
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
            'ketcoakredit' => 'required|array',
            'ketcoakredit.*' => 'required',
            'tgljatuhtempo' => 'required|array',
            'tgljatuhtempo.*' => 'required',
            // 'nowarkat' => 'required|array',
            // 'nowarkat.*' => 'required',
            'nominal_detail' => 'required|array',
            'nominal_detail.*' => 'required|numeric|gt:0',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required'
        ];
    }
}
