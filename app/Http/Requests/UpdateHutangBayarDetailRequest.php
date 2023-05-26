<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHutangBayarDetailRequest extends FormRequest
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
            'keterangan' => 'required|array',
            'keterangan.*' => 'required',
            'bayar' => 'required|array',
            'bayar.*' => 'required|numeric|gt:0',
            'sisa' => 'required|array',
            'sisa.*' => 'required|numeric|min:0',
        ];
    }
}
