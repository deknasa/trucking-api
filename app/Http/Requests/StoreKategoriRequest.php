<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreKategoriRequest extends FormRequest
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
            'kodekategori' => 'required',
            'subkelompok' => 'required',
            'statusaktif' => 'required'
        ];
    }
    
    public function attributes()
    {
        return[
            'kodekategori' => 'kode kategori',
            'subkelompok' => 'sub kelompok',
            'keterangan' => 'keterangan',
            'statusaktif' => 'status aktif'
        ];
    }


    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodekategori.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'subkelompok.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }    
}
