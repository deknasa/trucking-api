<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Http\Controllers\Api\ErrorController;

class StoreErrorRequest extends FormRequest
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
            'kodeerror' => 'required|unique:error',            
            'keterangan' => 'required|unique:error',
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
        // dd($controller->geterror('WI')->keterangan);
        return [
            'kodeerror.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'modifiedby.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'keterangan.unique' => ':attribute'.' '. $controller->geterror('SPI')->keterangan,
            'kodeerror.unique' => ':attribute'.' '. $controller->geterror('SPI')->keterangan,

        ];
    }
}
