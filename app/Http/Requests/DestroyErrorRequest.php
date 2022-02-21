<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class DestroyErrorRequest extends FormRequest
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
            'kodeerror' => 'required',
            'keterangan' => 'required',
            'modifiedby' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'kodeerror' => 'kode error',
            'keterangan' => 'keterangan',
            'modifiedby' => 'modified by',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'kodeerror.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'modifiedby.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,

        ];
    }
}
