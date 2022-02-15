<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreUserRequest extends FormRequest
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
            'user.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'name.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'password.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'cabang_id.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'karyawan_id.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'modifiedby.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,


        ];
    } 
}
