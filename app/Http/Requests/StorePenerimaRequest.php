<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaRequest extends FormRequest
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
            'namapenerima' => 'required',
            'npwp' => 'required',
            'noktp' => 'required',
            'statusaktif' => 'required|int',
            'statuskaryawan' => 'required|int',
        ];
    }

    public function attributes()
    {
        return [
            'namapenerima' => 'nama penerima',
            'npwp' => 'npwp',
            'noktp' => 'noktp',
            'statusaktif' => 'status aktif',
            'statuskaryawan' => 'status karyawan',
        ];
    }
}
