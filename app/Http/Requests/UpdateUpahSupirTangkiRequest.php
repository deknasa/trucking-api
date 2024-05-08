<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use App\Models\UpahSupirTangki;
use App\Rules\ExistKota;
use App\Rules\UniqueUpahSupirTangkiKotaSampaiEdit;
use App\Rules\UniqueUpahSupirTangkiSampaiEdit;
use App\Rules\validasiPenyesuaianUpahSupirTangki;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUpahSupirTangkiRequest extends FormRequest
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
        
        $upahSupir = new UpahSupirTangki();
        $dataUpahSupir = $upahSupir->findAll(request()->id);
        $check = (new UpahSupirTangki())->cekValidasi(request()->id);

        $parameter = new Parameter();
        $dataAktif = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $dataAktif = json_decode($dataAktif, true);
        foreach ($dataAktif as $item) {
            $statusAktif[] = $item['id'];
        }

        
        $kotadari_id = $this->kotadari_id;
        $rulesKotaDari_id = [];
        if ($kotadari_id != null) {
            if ($kotadari_id == 0) {
                $rulesKotaDari_id = [
                    'kotadari_id' => ['numeric', 'min:1', new ExistKota(), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotadari_id) : '']
                ];
            }
        } else if ($kotadari_id == null && $this->kotadari != '') {
            $rulesKotaDari_id = [
                'kotadari_id' => ['numeric', 'min:1', new ExistKota(), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotadari_id) : '']
            ];
        }

        $kotasampai_id = $this->kotasampai_id;
        $rulesKotaSampai_id = [];
        if ($kotasampai_id != null) {
            if ($kotasampai_id == 0) {
                $rulesKotaSampai_id = [
                    'kotasampai_id' => ['numeric', 'min:1', new UniqueUpahSupirTangkiSampaiEdit(), new ExistKota(), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotasampai_id) : '']
                ];
            }
        } else if ($kotasampai_id == null && $this->kotasampai != '') {
            $rulesKotaSampai_id = [
                'kotasampai_id' => ['numeric', 'min:1', new UniqueUpahSupirTangkiSampaiEdit(), new ExistKota(), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotasampai_id) : '']
            ];
        }

        $rules =  [
            'kotadari' => [($check['kondisi']) ? Rule::in($dataUpahSupir->kotadari) : ''],
            'kotasampai' => [($check['kondisi']) ? Rule::in($dataUpahSupir->kotasampai) : '', new UniqueUpahSupirTangkiKotaSampaiEdit()],
            'penyesuaian' => [new UniqueUpahSupirTangkiSampaiEdit(), new validasiPenyesuaianUpahSupirTangki(), ($check['kondisi']) ? Rule::in(trim($dataUpahSupir->penyesuaian)) : ''],
            'jarak' => ['required', 'numeric', 'gt:0'],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'tglmulaiberlaku' => [
                'required', 'date_format:d-m-Y',
            ],
            'gambar.*' => ['image', 'min:100'],
            'triptangki' => 'required|array',
            'triptangki.*' => 'required',
            'triptangki_id.*' => 'required',
            'nominalsupir.*' => ['required','numeric','min:0'],
        ];
        $rules = array_merge(
            $rules,
            $rulesKotaDari_id,
            $rulesKotaSampai_id,
        );
        return $rules;
    }
    public function attributes()
    {
        return [
            'kotadari' => 'kota dari',
            'kotasampai' => 'kota sampai',
            'statusaktif' => 'status aktif',
            'tglmulaiberlaku' => 'tanggal mulai berlaku',
            'triptangki.*' => 'trip',
            'nominalsupir.*' => 'nominal supir',
            'gambar.*' => 'gambar'
        ];
    }

}
