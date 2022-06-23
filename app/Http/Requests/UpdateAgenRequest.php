<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgenRequest extends FormRequest
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
            "keterangan" => "required",
            "statusaktif" => "required",
            "namaperusahaan" => "required",
            "alamat" => "required",
            "notelp" => "required",
            "nohp" => "required",
            "contactperson" => "required",
            "top" => "required",
            "statustas" => "required",
            "jenisemkl" => "required",
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
            "statustas" => "status tas",
            "jenisemkl" => "jenis emkl",
        ];
    }
}
