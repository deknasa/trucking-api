<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratPengantarRequest extends FormRequest
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
            'pelanggan_id' => 'required',
            'keterangan' => 'required',
            'dari_id' => 'required',
            'sampai_id' => 'required',
            'container_id' => 'required',
            'statuscontainer_id' => 'required',
            'trado_id' => 'required',
            'supir_id' => 'required',
            'agen_id' => 'required',
            'jenisorder_id' => 'required',
            'tarif_id' => 'required',
            'nosp' => 'required',
            'tglsp' => 'required',
            // 'nominalsupir' => 'required|numeric|min:1'
            // 'tujuantagih' => 'required',
        ];
    }
}
