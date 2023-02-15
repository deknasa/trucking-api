<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTradoRequest extends FormRequest
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
            'keterangan' => 'required',
            'statusaktif' => 'required',
            'tahun' => 'required',
            'merek' => 'required',
            'norangka' => 'required',
            'nomesin' => 'required',
            'nama' => 'required',
            'nostnk' => 'required',
            'alamatstnk' => 'required',
            'statusjenisplat' => 'required',
            'tglpajakstnk' => 'required',
            'tipe' => 'required',
            'jenis' => 'required',
            'isisilinder' => 'required',
            'warna' => 'required',
            'jenisbahanbakar' => 'required',
            'jumlahsumbu' => 'required',
            'jumlahroda' => 'required',
            'model' => 'required',
            'nobpkb' => 'required',
            'jumlahbanserap' => 'required',
            'statusgerobak' => 'required',
            'phototrado' => 'required|array',
            'phototrado.*' => 'required|image',
            'photobpkb' => 'required|array',
            'photobpkb.*' => 'required|image',
            'photostnk' => 'required|array',
            'photostnk.*' => 'required|image',
        ];
    }

    public function attributes()
    {
        return [
            'keterangan' => 'Keterangan',
            'statusaktif' => 'Status Aktif',
            'tahun' => 'Tahun',
            'merek' => 'Merek',
            'norangka' => 'No Rangka',
            'nomesin' => 'No Mesin',
            'nama' => 'Nama',
            'nostnk' => 'No STNK',
            'alamatstnk' => 'Alamat STNK',
            'statusjenisplat' => 'Jenis Plat',
            'tglpajakstnk' => 'Tgl Pajak STNK',
            'tipe' => 'Tipe',
            'jenis' => 'Jenis',
            'isisilinder' => 'Isi Silinder',
            'warna' => 'Warna',
            'jenisbahanbakar' => 'Jenis Bahan Bakar',
            'jumlahsumbu' => 'Jumlah Sumbu',
            'jumlahroda' => 'Jumlah Roda',
            'model' => 'Model',
            'nobpkb' => 'No BPKB',
            'jumlahbanserap' => 'Jumlah Ban Serap',
            'statusgerobak' => 'Status Gerobak'
        ];
    }
}
