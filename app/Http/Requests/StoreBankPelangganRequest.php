<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreBankPelangganRequest extends FormRequest
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
            'kodebank' => 'required',
            'namabank' => 'required',
            'keterangan' => 'required',
            'statusaktif' => 'required',
        ];
    }

    
    public function attributes()
    {
        return [
            'kodebank' => 'kode bank',
            'namabank' => 'nama bank',
            'statusaktif' => 'status aktif',
            'keterangan' => 'keterangan',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodebank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namabank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'keterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            
        ];
    }
}
