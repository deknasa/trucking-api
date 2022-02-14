<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class DestroyAcosRequest extends FormRequest
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
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'class.required' => 'class '. $controller->geterror(1)->keterangan,
            'method.required' => 'method '. $controller->geterror(1)->keterangan,
            'nama.required' => 'nama '. $controller->geterror(1)->keterangan,
            'modifiedby.required' => 'modifiedby '. $controller->geterror(1)->keterangan,

        ];
    }
}
