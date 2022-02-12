<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;


class UpdateErrorRequest extends FormRequest
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
            'keterangan' => 'required',
            'modifiedby' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'keterangan' => 'keterangan',
            'modifiedby' => 'modifiedby',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'keterangan.required' => 'Keterangan '. $controller->geterror(1)->keterangan,
            'modifiedby.required' => 'Modified by '. $controller->geterror(1)->keterangan,

        ];
    }

}
