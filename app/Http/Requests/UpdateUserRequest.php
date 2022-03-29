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
            'cabang_id' => 'required',
            'karyawan_id' => 'required',
            'statusaktif' => 'required',
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
        ];
    }
}
