<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use App\Models\TarifTangki;
use App\Rules\uniqueTujuanTarifTangkiEdit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTarifTangkiRequest extends FormRequest
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
        
        $tarif = new TarifTangki();
        $dataTarif = $tarif->findAll($this->id);
        $check = (new TarifTangki())->cekvalidasihapus(request()->id);
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
        $rules = [
            'tujuan' =>  ['required', ($check['kondisi']) ? Rule::in($dataTarif->tujuan) : ''],
            'penyesuaian' => [new uniqueTujuanTarifTangkiEdit(), ($check['kondisi']) ? Rule::in($dataTarif->penyesuaian) : ''],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'nominal' => ['required', 'numeric', 'gt:0'],
            'statuspenyesuaianharga' => ['required', Rule::in($statusPenyesuaian)],
            'tglmulaiberlaku' => [
                'required', 'date_format:d-m-Y',
            ],
            'kota' => ['required', ($check['kondisi']) ? Rule::in($dataTarif->kota) : ''],
        ];

        return [
            //
        ];
    }
}
