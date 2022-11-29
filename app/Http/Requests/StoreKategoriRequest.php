<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'keterangan' => 'required',
            'subkelompok' => 'required',
            'statusaktif' => 'required'
        ];
    }
    
    public function attributes()
    {
        return[
            'kodekategori' => 'kode kategori',
            'subkelompok' => 'sub kelompok',
            'statusaktif' => 'status aktif'
        ];
    }
}
