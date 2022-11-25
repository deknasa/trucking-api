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
            'container' => 'required',
            'nominal' => 'required|numeric|gt:0',
            'statusaktif' => 'required',
            'statussistemton' => 'required',
            'zona' => 'required',
            'kota' => 'required',
            'nominalton' => 'integer|min:0',
            'tglmulaiberlaku' => 'required',
            'tglakhirberlaku' => 'required',
            'statuspenyesuaianharga' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'statussistemton' => 'Status Sistem Ton',
            'tglmulaiberlaku' => 'Tanggal Mulai Berlaku',
            'tglakhirberlaku' => 'Tanggal Akhir Berlaku',
            'statuspenyesuaianharga' => 'Status Penyesuaian Harga'
        ];
    }

    public function messages() 
    {
        return [
            'nominal.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'nominalton.min' => 'Tidak boleh minus. minimal 0',
        ];
    }
}
