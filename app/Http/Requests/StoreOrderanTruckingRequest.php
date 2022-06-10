<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderanTruckingRequest extends FormRequest
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
            'container_id' => 'required',
            'agen_id' => 'required',
            'jenisorder_id' => 'required',
            'pelanggan_id' => 'required',
            'tarif_id' => 'required',
            'nojobemkl' => 'required',
            'nocont' => 'required',
            'noseal' => 'required',
            'statuslangsir' => 'required',
            'statusperalihan' => 'required',
        ];
    }
}
