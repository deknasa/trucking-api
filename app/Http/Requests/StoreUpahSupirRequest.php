<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpahSupirRequest extends FormRequest
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
            'kotadari_id' => 'required',
            'kotasampai_id' => 'required',
            'zona_id' => 'required',
            'jarak' => 'required',
            'statusaktif' => 'required',
            'statusluarkota' => 'required',
        ];
    }
}
