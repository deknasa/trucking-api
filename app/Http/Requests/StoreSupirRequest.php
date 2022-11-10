<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupirRequest extends FormRequest
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
            'namasupir' => 'required',
            'alamat' => 'required',
            'kota' => 'required',
            'telp' => 'required',
            'statusaktif' => 'required',
            'tglmasuk' => 'required',
            'tglexpsim' => 'required',
            'nosim' => 'required|min:12|max:12',
            'keterangan' => 'required',
            'noktp' => 'required|min:16|max:16',
            'nokk' => 'required|min:16|max:16',
            'statusluarkota' => 'required',
            'statuszonatertentu' => 'required',
            'zona_id' => 'required',
            'statusblacklist' => 'required',
            'tgllahir' => 'required',
            'tglterbitsim' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'namasupir' => 'Nama Supir',
            'alamat' => 'Alamat',
            'kota' => 'Kota',
            'telp' => 'Telp',
            'statusaktif' => 'Status Aktif',
            'tglmasuk' => 'Tanggal Masuk',
            'tglexpsim' => 'Tanggal Exp SIM',
            'nosim' => 'No SIM',
            'keterangan' => 'Keterangan',
            'noktp' => 'No KTP',
            'nokk' => 'No KK',
            'statusluarkota' => 'Status Luar Kota',
            'statuszonatertentu' => 'Status Zona Tertentu',
            'zona_id' => 'Zona',
            'statusblacklist' => 'Status Blacklist',
            'tgllahir' => 'Tanggal Lahir',
            'tglterbitsim' => 'Tanggal Terbit SIM',
        ];
    }
}
