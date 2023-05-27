<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class DestroyCabangRequest extends FormRequest
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
            'kodekota' => 'required',
            'keterangan' => 'required',
            'zona' => 'zona',
            'statusaktif' => 'required',
            'modifiedby' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'kodekota' => 'kode kota',
            'keterangan' => 'keterangan',
            'zona' => 'zona',
            'statusaktif' => 'status aktif',
            'modifiedby' => 'modified by'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'kodekota.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'zona.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'modifiedby.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,

        ];
    }
}