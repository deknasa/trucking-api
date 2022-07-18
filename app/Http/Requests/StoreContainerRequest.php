<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreContainerRequest extends FormRequest
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
            'kodecontainer' => 'required',
            'keterangan' => 'required',
            'statusaktif' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'kodecontainer' => 'Kode Container',
            'keterangan' => 'Keterangan',
            'statusaktif' => 'Status Aktif'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        
        return [
            'kodecontainer.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute '. $controller->geterror('WI')->keterangan,
        ];
    }
}
