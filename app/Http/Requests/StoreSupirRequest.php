<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupirRequest extends FormRequest
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
            'alamat' => 'required',
            'statusaktif' => 'required',
            // 'tglstnkmati' => 'required',
            // 'tglasuransimati' => 'required',
            // 'tahun' => 'required',
            // 'akhirproduksi' => 'required',
            // 'tglstandarisasi' => 'required',
            // 'tglserviceopname' => 'required',
            // 'statusstandarisasi' => 'required',
            // 'tglspeksimati' => 'required',
            // 'statusmutasi' => 'required',
            // 'statusvalidasikendaraan' => 'required',
            // 'mandor_id' => 'required',
        ];
    }
}
