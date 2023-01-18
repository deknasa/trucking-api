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
            'karyawan_id' => 'required',
            'dashboard' => 'required',
            'statusaktif' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'user' => 'user',
            'name' => 'nama user',
            'password' => 'password',
            'karyawan_id' => 'karyawan',
            'dashboard' => 'dashboard',
            'statusaktif' => 'status',
        ];
    }
}
