<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreUserRoleRequest extends FormRequest
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
            // 'user_id' => 'required|exists:user,id',
            'role_id' => 'required|array',
            'role_id.*' => 'required|int|exists:role,id',
            'status' => 'required|array',
            'status.*' => 'required|int'
        ];
    }

    public function attributes()
    {
        return [
            // 'user_id' => 'user',
            'role_id' => 'role',
        ];
    }
}
