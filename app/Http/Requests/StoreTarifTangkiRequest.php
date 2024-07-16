<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use App\Rules\uniqueTujuanTarifTangki;
use App\Rules\ValidasiTujuanKota;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTarifTangkiRequest extends FormRequest
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
        if (request()->from != '') {
            return [];
        }
        $parameter = new Parameter();
        $dataAktif = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $dataAktif = json_decode($dataAktif, true);
        foreach ($dataAktif as $item) {
            $statusAktif[] = $item['id'];
        }
        $statusaktif = $this->statusaktif;
        $rulesStatusAktif = [];
        if ($statusaktif != null) {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($statusAktif)]
            ];
        } else if ($statusaktif == null && $this->statusaktifnama != '') {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($statusAktif)]
            ];
        }

        $dataPenyesuaian = $parameter->getcombodata('PENYESUAIAN HARGA', 'PENYESUAIAN HARGA');
        $dataPenyesuaian = json_decode($dataPenyesuaian, true);
        foreach ($dataPenyesuaian as $item) {
            $statusPenyesuaian[] = $item['id'];
        }
        $statuspenyesuaianharga = $this->statuspenyesuaianharg;
        $rulesStatusPenyesuaianHarga = [];
        if ($statuspenyesuaianharga != null) {
            $rulesStatusPenyesuaianHarga = [
                'statuspenyesuaianharga' => ['required', Rule::in($statusPenyesuaian)]
            ];
        } else if ($statuspenyesuaianharga == null && $this->statuspenyesuaianharganama != '') {
            $rulesStatusPenyesuaianHarga = [
                'statuspenyesuaianharga' => ['required', Rule::in($statusPenyesuaian)]
            ];
        }

        $rules = [
            'nominal' => ['required', 'numeric', 'gt:0'],
            'kota' => ['required'],
            'tujuan' => ['required', new ValidasiTujuanKota()],
            'penyesuaian' => [new uniqueTujuanTarifTangki()],
            'statusaktifnama' => ['required'],
            'statuspenyesuaianharga' => ['required'],
            'tglmulaiberlaku' => [
                'required', 'date_format:d-m-Y',
            ],
        ];
        $rules = array_merge(
            $rules,
            $rulesStatusAktif,
            $rulesStatusPenyesuaianHarga
        );
        return $rules;
    }

    public function attributes()
    {
        return [
            'statusaktifnama' => 'Status Aktif',
            'tglmulaiberlaku' => 'Tanggal Mulai Berlaku',
            'statuspenyesuaianharganama' => 'Status Penyesuaian Harga'
        ];
    }
}
