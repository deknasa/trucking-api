<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotaDebetDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nominal_detail' => 'required|array',
            'nominal_detail.*' => 'required|gt:0|numeric',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required'
        ];
    }
}
