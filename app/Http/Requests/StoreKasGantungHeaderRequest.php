<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKasGantungHeaderRequest extends FormRequest
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
            'nobukti' => 'required',
            'tglbukti' => 'required',
            'penerima_id' => 'required',
            'nominal' => 'required',
            'keterangan_detail' => 'required',
        ];
    }

    public function attributes() {
        return [
            'nobukti' => 'No Bukti',
            'tgl' => 'Tanggal',
            'penerima_id' => 'Penerima',
        ];
    }
}
