<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;
use App\Models\Parameter;
use App\Rules\ValidationParameterInputValue;

class UpdateParameterRequest extends FormRequest
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
            'grp' => 'required',
            'subgrp' => 'required',
            'text' => 'required',
            // 'default' => 'required',
            'key' => 'required|array',
            'key.*' => 'required',
            'value' => 'required|array',
            'value.*' => [new ValidationParameterInputValue()]
        ];
    }

    public function attributes()
    {
        return [
            'grp' => 'group',
            'subgrp' => 'subgroup',
            'text' => 'name',
            'default' => 'default',
            'key.*' => 'key',
            'value.*' => 'value'
        ];
    }
}
