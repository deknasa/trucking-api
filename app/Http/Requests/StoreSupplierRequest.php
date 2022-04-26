<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
            'namasupplier' => 'required',
            'namakontak' => 'required',
            'alamat' => 'required',
            'coa_id' => 'required|int',
            'kota' => 'required',
            'kodepos' => 'required',
            'notelp1' => 'required',
            'notelp2' => 'required',
            'email' => 'required',
            'statussupllier' => 'required|int',
            'web' => 'required',
            'namapemilik' => 'required',
            'jenisusaha' => 'required',
            'top' => 'required|int',
            'bank' => 'required',
            'rekeningbank' => 'required',
            'namabank' => 'required',
            'jabatan' => 'required',
            'statusdaftarharga' => 'required|int',
            'kategoriusaha' => 'required',
            'bataskredit' => 'required|numeric',
        ];
    }
}
