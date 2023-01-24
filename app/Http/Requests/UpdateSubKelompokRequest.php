<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class UpdateSubKelompokRequest extends FormRequest
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
            'kodesubkelompok' => 'required',
            'kelompok' => 'required',
            'keterangan' => 'required',
            'statusaktif' => 'required|numeric',
        ];
    }

    public function attributes()
    {
        return [
            'kodesubkelompok' => 'kode subkelompok',
            'kelompok' => 'kelompok',
            'keterangan' => 'keterangan',
            'statusaktif' => 'status aktif',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodesubkelompok.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kelompok.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }  
}
