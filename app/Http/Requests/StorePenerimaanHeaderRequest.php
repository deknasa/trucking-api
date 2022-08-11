<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanHeaderRequest extends FormRequest
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
            // 'keterangan' => 'required',
            // 'tgllunas' => 'required',
            // 'cabang_id' => 'required',
            // 'statuskas' => 'required',
            // 'noresi' => 'required',
            // 'nowarkat' => 'required',
            // 'tgljatuhtempo' => 'required',
            // 'nominal' => 'required',
            // 'keterangan_detail' => 'required',
            // 'bank_id' => 'required',
            // 'bankpelanggan_id' => 'required',
            // 'jenisbiaya' => 'required',
        ];
    }
    public function attributes()
    {
        return [];
    }
}
