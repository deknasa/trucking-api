<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpahSupirRincianRequest extends FormRequest
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
            'container' => 'required|array',
            'container.*' => 'required',
            'statuscontainer' => 'required|array',
            'statuscontainer.*' => 'required',
            'nominalsupir' => 'required|array',
            'nominalsupir.*' => 'required|numeric|gt:0',
            'nominalkenek' => 'required|array',
            'nominalkenek.*' => 'required|numeric|gt:0',
            'nominalkomisi' => 'required|array',
            'nominalkomisi.*' => 'required|numeric|gt:0',
            'nominaltol' => 'required|array',
            'nominaltol.*' => 'required|numeric|gt:0',
            'liter' => 'required|array',
            'liter.*' => 'required|numeric|gt:0',
        ];
    }
}
