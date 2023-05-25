<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKotaRequest extends FormRequest
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
        //  dd($this->id);
        return [
            'kodekota' => ['required',Rule::unique('kota')->whereNotIn('id', [$this->id])],
            'keterangan' => ['nullable',Rule::unique('kota')->whereNotIn('id', [$this->id])],
            'zona' => 'required',
            'statusaktif' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'kodekota' => 'kode kota',
            'statusaktif' => 'statusaktif'
        ];
    }
}
