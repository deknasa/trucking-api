<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            'statusaktif' => 'required|int|exists:parameter,id',
            'tglmasuk' => 'required',
            'tglexpsim' => 'required',
            'nosim' => 'required|min:12|max:12',
            'noktp' => 'required|min:16|max:16',
            'nokk' => 'required|min:16|max:16',
            'tgllahir' => 'required',
            'tglterbitsim' => 'required',
            'photosupir' => 'required|array',
            'photosupir.*' => 'required|image',
            'photoktp' => 'required|array',
            'photoktp.*' => 'required|image',
            'photosim' => 'required|array',
            'photosim.*' => 'required|image',
            'photokk' => 'required|array',
            'photokk.*' => 'required|image',
            'photoskck' => 'required|array',
            'photoskck.*' => 'required|image',
            'photodomisili' => 'required|array',
            'photodomisili.*' => 'required|image'
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
            'noktp' => 'No KTP',
            'nokk' => 'No KK',
            'tgllahir' => 'Tanggal Lahir',
            'tglterbitsim' => 'Tanggal Terbit SIM',
        ];
    }
    public function messages() 
    {
        return [
            'noktp.max' => 'Max. 16 karakter',
            'noktp.min' => 'Min. 16 karakter',
            'nokk.max' => 'Max. 16 karakter',
            'nokk.min' => 'Min. 16 karakter',
            'nosim.max' => 'Max. 12 karakter',
            'nosim.min' => 'Min. 12 karakter',
        ];
    }
}
