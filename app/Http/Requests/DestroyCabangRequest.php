<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class DestroyCabangRequest extends FormRequest
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
            'kodecabang' => 'required',
            'namacabang' => 'required',
            'statusaktif' => 'required',
            'modifiedby' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'kodecabang' => 'kodecabang',
            'namacabang' => 'namacabang',
            'statusaktif' => 'statusaktif',
            'modifiedby' => 'modifiedby',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'kodecabang.required' => 'kodecabang '. $controller->geterror(1)->keterangan,
            'namacabang.required' => 'namacabang '. $controller->geterror(1)->keterangan,
            'statusaktif.required' => 'statusaktif '. $controller->geterror(1)->keterangan,
            'modifiedby.required' => 'modifiedby '. $controller->geterror(1)->keterangan,

        ];
    }
}
