<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;

class UpdateUserAclRequest extends FormRequest
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
            'user_id' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'aco_id' => 'aco id',
            'user_id' => 'user id',
        ];
    }
}
