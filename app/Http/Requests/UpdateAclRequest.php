<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class UpdateAclRequest extends FormRequest
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
            'aco_id' => 'required',
            'role_id' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'aco_id' => 'aco id',
            'role_id' => 'role id',
        ];
    }
}
