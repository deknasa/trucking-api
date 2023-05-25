<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class UpdatePelangganRequest extends FormRequest
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
            'kodepelanggan' => 'required',
            'namapelanggan' => 'required',
            'telp' => 'required|min:12|max:13',
            'alamat' => 'required',
            'kota' => 'required',
            'kodepos' => 'required|min:5|max:5',
            'statusaktif' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'kodepelanggan' => 'kode pelanggan',
            'namapelanggan' => 'nama pelanggan',
            'kodepos' => 'kode pos',
            'telp' => 'no telpon',
            'alamat' => 'alamat',
            'kota' => 'kota', 
            'keterangan' => 'keterangan',           
            'statusaktif' => 'status aktif',  
        ];

        
    }

    
    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodepelanggan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namapelanggan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kodepos.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'telp.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'alamat.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kota.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'telp.min' => 'min. 12 karakter',
            'telp.max' => 'max. 13 karakter',
            'kodepos.min' => 'min. 5 karakter',
            'kodepos.max' => 'max. 5 karakter',
        ];
    }  
}
