<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHariLiburRequest extends FormRequest
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
            'tgl' => 'required',
            'keterangan' => 'required',
            'statusaktif' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'tgl' => 'Tanggal Libur',
            'keterangan' => 'Keterangan',
            'statusaktif' => 'Status Aktif'
        ];
    }
}
