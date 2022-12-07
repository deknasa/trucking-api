<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class ParameterRequest extends FormRequest
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
            'key' => 'required|array',
            'key.*' => 'required',
            'value' => 'required|array',
            'value.*' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'grp' => 'group',
            'subgrp' => 'subgroup',
            'text' => 'name',
            'key.*' => 'key',
            'value.*' => 'value'
        ];
    }
}
