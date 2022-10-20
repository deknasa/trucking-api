<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGajiSupirHeaderRequest extends FormRequest
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
            //
            'supir_id' => 'required',
            'tgldari' => 'required',
            'tglsampai' => 'required',
            'keterangan' => 'required',
            'tglbukti' => 'required',
        ];
    }

    public function attributes() {
        return [
            'supir_id' => 'Supir',
            'tgldari' => 'Tanggal Dari',
            'tglsampai' => 'Tanggal Sampai',
            'keterangan' => 'Keterangan',
            'tglbukti' => 'Tanggal Bukti'
        ];
    }
    
}
