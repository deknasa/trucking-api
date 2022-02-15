<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class DestroyRoleRequest extends FormRequest
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
            'rolename' => 'required',
            'modifiedby' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'rolename' => 'nama role',
            'modifiedby' => 'modified by'

        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'rolename.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'modifiedby.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,

        ];
    }    
}
