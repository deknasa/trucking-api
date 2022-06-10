<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProsesAbsensiSupirRequest extends FormRequest
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
            'tglbukti' => 'required',
            'keterangan' => 'required',
            'absensisupir_nobukti' => 'required|unique:prosesabsensisupir',
        ];
    }

    public function messages()
    {
        return [
            'absensisupir_nobukti.unique' => 'Nomor Absensi Sudah Digunakan',
        ];
    }
}
