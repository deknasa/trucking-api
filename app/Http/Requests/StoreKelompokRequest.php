<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreKelompokRequest extends FormRequest
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
            'kodekelompok' => 'required',
            'keterangan' => 'required',
            'statusaktif' => 'required'
        ];
    }
    
    public function attributes()
    {
        return[
            'kodekelompok' => 'kode kelompok',
            'statusaktif' => 'status aktif',
            'keterangan' => 'keterangan',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodekelompok.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }

}
