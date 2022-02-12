<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class UpdateUserRequest extends FormRequest
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
            'password' => 'required',
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
            'name' => 'name',
            'password' => 'password',
            'cabang_id' => 'cabang_id',
            'karyawan_id' => 'karyawan_id',
            'dashboard' => 'dashboard',
            'statusaktif' => 'statusaktif',
            'modifiedby' => 'modifiedby'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'user.required' => 'user '. $controller->geterror(1)->keterangan,
            'name.required' => 'name '. $controller->geterror(1)->keterangan,
            'password.required' => 'password '. $controller->geterror(1)->keterangan,
            'cabang_id.required' => 'cabang_id '. $controller->geterror(1)->keterangan,
            'karyawan_id.required' => 'karyawan_id '. $controller->geterror(1)->keterangan,
            'statusaktif.required' => 'statusaktif '. $controller->geterror(1)->keterangan,
            'modifiedby.required' => 'modifiedby '. $controller->geterror(1)->keterangan,


        ];
    } 
}
