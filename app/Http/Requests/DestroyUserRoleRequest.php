<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class DestroyUserRoleRequest extends FormRequest
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
            'user_id' => 'required',
            'role_id' => 'required',
            // 'modifiedby' => 'required'

        ];
    }

    public function attributes()
    {
        return [
            'user_id' => 'user id',
            'role_id' => 'role id',
            // 'modifiedby' => 'modified by'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'user_id.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'role_id.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            // 'modifiedby.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,


        ];
    }
}
