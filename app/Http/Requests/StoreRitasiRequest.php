<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRitasiRequest extends FormRequest
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
            'tglbukti' => 'required',
            'statusritasi' => 'required',
            'suratpengantar_nobukti' => 'required',
            'dari_id' => 'required',
            'sampai_id' => 'required',
            'trado_id' => 'required',
            'supir_id' => 'required',
        ];
    }
}
