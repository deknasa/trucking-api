<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }

        $dataTas = $parameter->getcombodata('STATUS TAS', 'STATUS TAS');
        $dataTas = json_decode($dataTas, true);
        foreach ($dataTas as $item) {
            $statusTas[] = $item['id'];
        }


        return [
            "kodeagen" => "required|unique:agen",
            "namaagen" => "required|unique:agen",
            "statusaktif" => ['required', Rule::in($status),'numeric', 'min:1'],
            "namaperusahaan" => "required",
            "alamat" => "required",
            "notelp" => "required|unique:agen|min:11|max:13",
            "contactperson" => "required",
            // "keterangancoa" => "required",
            // "keterangancoapendapatan" => "required",
            "top" => "required|numeric|gt:0|max:999",
            "statustas" => ["required",Rule::in($statusTas),'numeric','min:1'],
            // "keteranganjenisemkl" => "required",
        ];
    }

    public function attributes()
    {
        return [
            "kodeagen" => "kode agen (emkl)",
            "namaagen" => "nama agen (emkl)",
            "statusaktif" => "status aktif",
            "namaperusahaan" => "nama perusahaan",
            "notelp" => "no telepon/handphone",
            "contactperson" => "nama kontak",
            "top" => "status pembayaran (top)",
            "statustas" => "status tas",
            "keterangancoa" => "keterangan coa",
            "keterangancoapendapatan" => "keterangan coa pendapatan",
            // "keteranganjenisemkl" => "jenis emkl",
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
            'contactperson.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'top.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'jenisusaha.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statustas.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            // 'keteranganjenisemkl.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,

            'top.gt' => ':attribute' . ' ' . $controller->geterror('GT-ANGKA-0')->keterangan,
            'kodeagen.unique' => ':attribute' . ' ' . $controller->geterror('SPI')->keterangan,
            'namaagen.unique' => ':attribute' . ' ' . $controller->geterror('SPI')->keterangan,
            'notelp.unique' => ':attribute' . ' ' . $controller->geterror('SPI')->keterangan,
            'notelp.min' => 'Min 11 Karakter',
            'notelp.max' => 'Max 13 Karakter',
            'nohp.min' => 'Min 11 Karakter',
            'nohp.max' => 'Max 13 Karakter',
        ];
    }    
}
