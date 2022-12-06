<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlatBayarRequest extends FormRequest
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
            'kodealatbayar' => 'required',
            'namaalatbayar' => 'required',
            'keterangan' => 'required',
            'statuslangsunggcair' => 'required',
            'statusdefault' => 'required',
            'bank' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'kodealatbayar' => 'kode alat bayar',
            'namaalatbayar' => 'nama alat bayar',
            'statuslangsunggcair' => 'status langsung cair',
            'statusdefault' => 'statusdefault',
        ];
    }
}
