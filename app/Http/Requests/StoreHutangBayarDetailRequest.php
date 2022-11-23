<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHutangBayarDetailRequest extends FormRequest
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
            'hutang_id' => 'required',
            'keterangandetail' => 'required|array',
            'keterangandetail.*' => 'required',
            'bayar' => 'required|array',
            'bayar.*' => 'required|numeric|gt:0',
            'alatbayar' => 'required|array',
            'alatbayar.*' => 'required',
            'tglcair' => 'required|array',
            'tglcair.*' =>'required'
        ];
    }
}
