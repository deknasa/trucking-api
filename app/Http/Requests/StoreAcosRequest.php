<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreAcosRequest extends FormRequest
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
            'class' => 'required',
            'method' => 'required',
            'nama' => 'required',
            'modifiedby' => 'required',

        ];
    }

    public function attributes()
    {
        return [
            'class' => 'class',
            'method' => 'method',
            'nama' => 'nama',
            'modifiedby' => 'modified by',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'class.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'method.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'nama.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'modifiedby.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
        ];
    }
}
