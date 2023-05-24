<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreAlatBayarRequest extends FormRequest
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
            'kodealatbayar' => 'required',
            'namaalatbayar' => 'required',
            'statuslangsungcair' => 'required',
            'statusdefault' => 'required',
            'statusaktif' => 'required',
            'bank' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'kodealatbayar' => 'kode alat bayar',
            'namaalatbayar' => 'nama alat bayar',
            'statuslangsungcair' => 'status langsung cair',
            'statusdefault' => 'status default',
            'statusaktif' => 'status aktif',
            'keterangan' => 'keterangan',
            'bank' => 'nama bank',
        ];
        
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodealatbayar.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namaalatbayar.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statuslangsungcair.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusdefault.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'bank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
           
        ];
    }  
}
