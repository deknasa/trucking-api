<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreBankRequest extends FormRequest
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
            'coa' => 'required',
            'tipe' => 'required',
            'statusaktif' => 'required',
            'formatpenerimaan' => 'required',
            'formatpengeluaran' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'kodebank' => 'kode bank',
            'namabank' => 'nama bank',
            'statusaktif' => 'status aktif',
            'coa' => 'kode perkiraan',
            'tipe' => 'tipe',
            'formatpenerimaan' => 'format penerimaan',
            'formatpengeluaran' => 'format pengeluaran',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodebank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namabank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'tipe.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'formatpenerimaan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'formatpengeluaran.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
