<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranDetailRequest extends FormRequest
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
            'alatbayar_id' => 'required|array',         
            'nowarkat' => 'required|array',
            'tgljatuhtempo' => 'required|array',
            'nominal' => 'required|array',
            'coadebet' => 'required|array',
            'keterangan_detail' => 'required|array',
            'bulanbeban' => 'required|array',
        ];
    }
}
