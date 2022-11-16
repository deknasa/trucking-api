<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanDetailRequest extends FormRequest
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
            'nowarkat' => 'required|array',
            'nowarkat.*' => 'required'
            // 'tgljatuhtempo' => 'required|array',
            // 'nominal' => 'required|array',
            // 'keterangan_detail' => 'required|array',
            // 'coakredit' => 'required|array',
            // 'bankpelanggan_id' => 'required|array',
            // 'jenisbiaya' => 'required|array',
        ];
    }
}
