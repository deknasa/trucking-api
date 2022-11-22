<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceInDetailRequest extends FormRequest
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
            'mekanik' => 'required|array',
            'mekanik.*' => 'required',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required'
        ];
    }
}
