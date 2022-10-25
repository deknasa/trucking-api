<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePelunasanPiutangDetailRequest extends FormRequest
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
            'bayarppd' => 'required|array',
            'bayarppd.*' => 'required|numeric|gt:0',
            'keterangandetailppd' => 'required|array',
            'keterangandetailppd.*' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'bayarppd' => 'Nominal Bayar',
            'keterangandetailppd' => 'Keterangan',
        ];
    }
   
}
