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
            
            'role_ids' => 'array',
            'role_ids.*' => 'required|int|exists:role,id',
            
        ];
    }

    public function attributes()
    {
        return [
            'role_ids' => 'role',
            'role_ids.*' => 'role',
        ];
    }
}
