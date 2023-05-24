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
            'telp' => 'required',
            'alamat' => 'required',
            'kota' => 'required',
            'kodepos' => 'required',
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
        ];
    }  
}
