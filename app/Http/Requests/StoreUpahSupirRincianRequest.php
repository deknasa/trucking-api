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
            'container_id' => 'required',
            'statuscontainer_id' => 'required',
            'nominalsupir' => 'required',
            'nominalkenek' => 'required',
            'nominalkomisi' => 'required',
            'nominaltol' => 'required',
            'liter' => 'required',
        ];
    }
}
