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
        $dataPenyesuaian = $parameter->getcombodata('PENYESUAIAN HARGA', 'PENYESUAIAN HARGA');
        $dataPenyesuaian = json_decode($dataPenyesuaian, true);
        foreach ($dataPenyesuaian as $item) {
            $statusPenyesuaian[] = $item['id'];
        }
        return [
            'nominal' => ['required', 'numeric', 'gt:0'],
            'kota' => ['required'],
            'tujuan' => ['required', new ValidasiTujuanKota()],
            'penyesuaian' => [new uniqueTujuanTarifTangki()],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'statuspenyesuaianharga' => ['required', Rule::in($statusPenyesuaian)],
            'tglmulaiberlaku' => [
                'required', 'date_format:d-m-Y',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'statusaktif' => 'Status Aktif',
            'tglmulaiberlaku' => 'Tanggal Mulai Berlaku',
            'statuspenyesuaianharga' => 'Status Penyesuaian Harga'
        ];
    }
}
