<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'modifiedby' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'aco_id' => 'aco_id',
            'user_id' => 'user_id',
        ];
    }
    public function messages()
    {
        return [
            'user_id.required' => 'User id Wajib diisi',
        ];
    }
}
