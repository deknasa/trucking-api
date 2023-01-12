<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGajiSupirHeaderRequest extends FormRequest
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
            'supir' => 'required',
            'tgldari' => 'required',
            'tglsampai' => 'required',
            'tglbukti' => 'required',
        ];
    }

    public function attributes() {
        return [
            'tgldari' => 'Tanggal Dari',
            'tglsampai' => 'Tanggal Sampai',
            'tglbukti' => 'Tanggal Bukti'
        ];
    }
}
