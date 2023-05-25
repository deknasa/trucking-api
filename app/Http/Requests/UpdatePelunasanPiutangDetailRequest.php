<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePelunasanPiutangDetailRequest extends FormRequest
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
            'piutang_id' => 'required',
            'bayar' => 'required|array',
            'bayar.*' => 'required|numeric|gt:0',
            'keterangan' => 'required|array',
            'keterangan.*' => 'required'
        ];
    }
}
