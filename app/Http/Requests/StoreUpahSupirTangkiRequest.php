<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use App\Rules\ExistKota;
use App\Rules\UniqueUpahSupirTangkiKotaSampai;
use App\Rules\UniqueUpahSupirTangkiSampai;
use App\Rules\validasiPenyesuaianUpahSupirTangki;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpahSupirTangkiRequest extends FormRequest
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
        

        $kotadari_id = $this->kotadari_id;
        $rulesKotaDari_id = [];
        if ($kotadari_id != null) {
            if ($kotadari_id == 0) {
                $rulesKotaDari_id = [
                    'kotadari_id' => ['numeric', 'min:1', new ExistKota()]
                ];
            }
        } else if ($kotadari_id == null && $this->kotadari != '') {
            $rulesKotaDari_id = [
                'kotadari_id' => ['numeric', 'min:1', new ExistKota()]
            ];
        }

        $kotasampai_id = $this->kotasampai_id;
        $rulesKotaSampai_id = [];
        if ($kotasampai_id != null) {
            if ($kotasampai_id == 0) {
                $rulesKotaSampai_id = [
                    'kotasampai_id' => ['numeric', 'min:1', new UniqueUpahSupirTangkiSampai(), new ExistKota()]
                ];
            }
        } else if ($kotasampai_id == null && $this->kotasampai != '') {
            $rulesKotaSampai_id = [
                'kotasampai_id' => ['numeric', 'min:1', new UniqueUpahSupirTangkiSampai(), new ExistKota()]
            ];
        }
        $rules =  [
            'kotadari' => ['required'],
            'kotasampai' => [new UniqueUpahSupirTangkiKotaSampai()],
            'penyesuaian' => [new UniqueUpahSupirTangkiSampai(), new validasiPenyesuaianUpahSupirTangki()],
            'jarak' => ['required', 'numeric', 'gt:0',],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'tglmulaiberlaku' => [
                'required', 'date_format:d-m-Y',
            ],
            'triptangki' => 'required|array',
            'triptangki.*' => 'required',
            'triptangki_id.*' => 'required',
            'nominalsupir.*' => ['required','numeric','min:0'],
            
        ];
        $rulesGambar = [];
        if (request()->from == null) {
            $rulesGambar = [
                'gambar.*' => ['image','min:100']
            ];
        }
        $rules = array_merge(
            $rules,
            $rulesKotaDari_id,
            $rulesKotaSampai_id,
            $rulesGambar
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
            'nominalsupir.*' => 'nominal supir'
        ];
    }
}
