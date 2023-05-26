<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengembalianKasGantungDetailRequest extends FormRequest
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
            'kasgantungdetail_id' => 'required',
            'nominal' => 'required|array',
            'nominal.*' => 'required|numeric|gt:0',
            'keterangandetail' => 'required|array',
            'keterangandetail.*' => 'required',
            'coadetail' => 'required|array',
            'coadetail.*' => 'required',
            'sisa' => 'required|array',
            'sisa.*' => 'required|numeric|min:0',
        ];
    }
}
