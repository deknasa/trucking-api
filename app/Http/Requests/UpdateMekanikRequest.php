<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class UpdateMekanikRequest extends FormRequest
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
            'namamekanik' => 'required',
            'keterangan' => 'required',
            'statusaktif' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'namamekanik' => 'nama mekanik',
            'keterangan' => 'keterangan',
            'statusaktif' => 'status',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        
        return [
            'namamekanik.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute '. $controller->geterror('WI')->keterangan,
        ];
    }}
