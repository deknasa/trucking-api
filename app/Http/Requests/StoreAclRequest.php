<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreAclRequest extends FormRequest
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
            'aco_ids' => 'required|array',
            'aco_ids.*' => 'required|int|exists:acos,id',
        ];
    }

    public function attributes()
    {
        return [
            'aco_ids' => 'aco',
            'aco_ids.*' => 'aco',
        ];
    }
}
