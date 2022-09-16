<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTarifRequest extends FormRequest
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
            'tujuan' => 'required',
            'container_id' => 'required',
            'nominal' => 'required|numeric',
            'statusaktif' => 'required',
            'tujuanasal' => 'required',
            'sistemton' => 'required',
            'zona_id' => 'required',
            'kota_id' => 'required',
            'nominalton' => 'required|numeric',
            'tglberlaku' => 'required',
            'statuspenyesuaianharga' => 'required',
        ];
    }
}
