<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTarifRequest extends FormRequest
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
            'tujuan' => 'required',
            'container_id' => 'required',
            'nominal' => 'required|numeric|gt:0',
            'statusaktif' => 'required',
            'tujuanasal' => 'required',
            'statussistemton' => 'required',
            'zona_id' => 'required',
            'kota_id' => 'required',
            'nominalton' => 'required|numeric|gt:0',
            'tglmulaiberlaku' => 'required',
            'tglakhirberlaku' => 'required',
            'statuspenyesuaianharga' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'container_id' => 'Container',
            'tujuanasal' => 'Tujuan Asal',
            'statussistemton' => 'Status Sistem Ton',
            'zona_id' => 'Zona',
            'kota_id' => 'Kota',
            'nominalton' => 'Nominal Ton',
            'tglmulaiberlaku' => 'Tanggal Mulai Berlaku',
            'tglakhirberlaku' => 'Tanggal Akhir Berlaku',
            'statuspenyesuaianharga' => 'Status Penyesuaian Harga'
        ];
    }

    public function messages() 
    {
        return [
            'nominal.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'nominalton.gt' => 'Nominal Ton Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
