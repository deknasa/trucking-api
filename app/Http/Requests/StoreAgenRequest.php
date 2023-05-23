<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgenRequest extends FormRequest
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
            "kodeagen" => "required",
            "namaagen" => "required",
            "statusaktif" => "required",
            "namaperusahaan" => "required",
            "alamat" => "required",
            "notelp" => "required",
            "nohp" => "required",
            "contactperson" => "required",
            "top" => "required|numeric|gt:0",
            "statustas" => "required",
        ];
    }

    public function attributes()
    {
        return [
            "kodeagen" => "kode agen",
            "namaagen" => "nama agen",
            "statusaktif" => "status",
            "namaperusahaan" => "nama perusahaan",
            "notelp" => "no telp",
            "nohp" => "no hp",
            "contactperson" => "contact person",
            "top" => "top",
            "statustas" => "status tas",
        ];
    }


    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodeagen.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namaagen.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namaperusahaan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'alamat.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'notelp.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'nohp.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'contactperson.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'top.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'jenisusaha.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statustas.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'top.gt' => ':attribute' . ' ' . $controller->geterror('GT-ANGKA-0')->keterangan,
        ];
    }    
}
