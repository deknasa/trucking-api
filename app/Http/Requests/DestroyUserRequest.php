<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class DestroyUserRequest extends FormRequest
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
            'user' => 'required',
            'name' => 'required',
            'cabang_id' => 'required',
            'karyawan_id' => 'required',
            'statusaktif' => 'required',
            'modifiedby' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'user' => 'user',
            'name' => 'nama user',
            'password' => 'password',
            'cabang_id' => 'cabang',
            'karyawan_id' => 'karyawan',
            'dashboard' => 'dashboard',
            'statusaktif' => 'status',
            'modifiedby' => 'modified by'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'user.required' => ':attribute'.' '. $controller->geterror(1)->keterangan,
            'name.required' => ':attribute'.' '. $controller->geterror(1)->keterangan,
            'cabang_id.required' => ':attribute'.' '. $controller->geterror(1)->keterangan,
            'karyawan_id.required' => ':attribute'.' '. $controller->geterror(1)->keterangan,
            'statusaktif.required' => ':attribute'.' '. $controller->geterror(1)->keterangan,
            'modifiedby.required' => ':attribute'.' '. $controller->geterror(1)->keterangan,


        ];
    }
}
