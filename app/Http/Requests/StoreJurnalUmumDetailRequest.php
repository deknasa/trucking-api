<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJurnalUmumDetailRequest extends FormRequest
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
            'coadebet_detail' => 'required|array',
            'coadebet_detail.*' => 'required',
            'coakredit_detail' => 'required|array',
            'coakredit_detail.*' => 'required',
            'nominal_detail' => 'required|array',
            'nominal_detail.*' => 'required',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required'
        ];
    }
}
