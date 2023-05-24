<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreSupplierRequest extends FormRequest
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
            'namasupplier' => 'required',
            'namakontak' => 'required',
            'alamat' => 'required',
            'kota' => 'required',
            'kodepos' => 'required',
            'notelp1' => 'required',
            'web' => 'required',
            'email' => 'required|email',
            'statusaktif' => 'required|numeric',
            'namapemilik' => 'required',
            'jenisusaha' => 'required',
            'bank' => 'required',
            'coa' => 'required',
            'rekeningbank' => 'required',
            'namarekening' => 'required',
            'jabatan' => 'required',
            'statusdaftarharga' => 'required|numeric',
            'kategoriusaha' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'namasupplier' => 'nama supplier',
            'namakontak' => 'nama kontak',
            'alamat' => 'alamat',
            'kota' => 'kota',
            'kodepos' => 'kode pos',
            'notelp1' => 'no telp 1',
            'web' => 'web',
            'email' => 'email',
            'statusaktif' => 'status aktif',
            'namapemilik' => 'nama pemilik',
            'jenisusaha' => 'jenis usaha',
            'bank' => 'bank',
            'coa' => 'coa',
            'rekeningbank' => 'rekening bank',
            'namarekening' => 'nama rekening',
            'jabatan' => 'jabatan',
            'statusdaftarharga' => 'status daftar arga',
            'kategoriusaha' => 'karegori usaha',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'namasupplier.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namakontak.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'alamat.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kota.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kodepos.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'notelp1.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'web.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'email.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namapemilik.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'jenisusaha.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'bank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'rekeningbank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namarekening.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'jabatan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusdaftarharga.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kategoriusaha.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'email.email' => ':attribute' . ' ' . $controller->geterror('EMAIL')->keterangan,
        ];
    }
}
