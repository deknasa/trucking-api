<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\UniqueUpahSupir;
use App\Rules\UniqueUpahSupirDari;
use App\Rules\UniqueUpahSupirSampai;
use Illuminate\Validation\Rule;

class StoreUpahSupirRequest extends FormRequest
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
        $parameter = new Parameter();
        $dataAktif = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $dataAktif = json_decode($dataAktif, true);
        foreach ($dataAktif as $item) {
            $statusAktif[] = $item['id'];
        }
        $dataLuarKota = $parameter->getcombodata('UPAH SUPIR LUAR KOTA', 'UPAH SUPIR LUAR KOTA');
        $dataLuarKota = json_decode($dataLuarKota, true);
        foreach ($dataLuarKota as $item) {
            $statusLuarKota[] = $item['id'];
        }

        $parent_id = $this->parent_id;
        $rulesParent_id = [];
        if ($parent_id != null) {
            if ($parent_id == 0) {
                $rulesParent_id = [
                    'parent_id' => ['required', 'numeric', 'min:1']
                ];
            } else {
                if ($this->parent == '') {
                    $rulesParent_id = [
                        'parent' => ['required']
                    ];
                }
            }
        } else if ($parent_id == null && $this->parent != '') {
            $rulesParent_id = [
                'parent_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $tarif_id = $this->tarif_id;
        $rulesTarif_id = [];
        if ($tarif_id != null) {
            if ($tarif_id == 0) {
                $rulesTarif_id = [
                    'tarif_id' => ['required', 'numeric', 'min:1']
                ];
            } else {
                if ($this->tarif == '') {
                    $rulesTarif_id = [
                        'tarif' => ['required']
                    ];
                }
            }
        } else if ($tarif_id == null && $this->tarif != '') {
            $rulesTarif_id = [
                'tarif_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $zona_id = $this->zona_id;
        $rulesZona_id = [];
        if ($zona_id != null) {
            if ($zona_id == 0) {
                $rulesZona_id = [
                    'zona_id' => ['required', 'numeric', 'min:1']
                ];
            } else {
                if ($this->zona == '') {
                    $rulesZona_id = [
                        'zona' => ['required']
                    ];
                }
            }
        } else if ($zona_id == null && $this->zona != '') {
            $rulesZona_id = [
                'zona_id' => ['required', 'numeric', 'min:1']
            ];
        }
        
        $kotadari_id = $this->kotadari_id;
        $rulesKotaDari_id = [];
        if ($kotadari_id != null) {
            if ($kotadari_id == 0) {
                $rulesKotaDari_id = [
                    'kotadari_id' => ['required', 'numeric', 'min:1', new UniqueUpahSupirDari()]
                ];
            } 
        } else if ($kotadari_id == null && $this->kotadari != '') {
            $rulesKotaDari_id = [
                'kotadari_id' => ['required', 'numeric', 'min:1', new UniqueUpahSupirDari()]
            ];
        }

        $kotasampai_id = $this->kotasampai_id;
        $rulesKotaSampai_id = [];
        if ($kotasampai_id != null) {
            if ($kotasampai_id == 0) {
                $rulesKotaSampai_id = [
                    'kotasampai_id' => ['required', 'numeric', 'min:1', new UniqueUpahSupirSampai()]
                ];
            } 
        } else if ($kotasampai_id == null && $this->kotadari != '') {
            $rulesKotaSampai_id = [
                'kotasampai_id' => ['required', 'numeric', 'min:1', new UniqueUpahSupirSampai()]
            ];
        }

        $parameter = new Parameter();
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglBatasAkhir = (date('Y') + 1) . '-01-01';
        $rules =  [
            'kotadari' => ['required'],
            'kotasampai' => ['required'],
            'jarak' => ['required','numeric','min:0','max:'. (new ParameterController)->getparamid('BATAS KM UPAH SUPIR','BATAS KM UPAH SUPIR')->text],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'statusluarkota' => ['required', Rule::in($statusLuarKota)],
            'tglmulaiberlaku' => ['required','date_format:d-m-Y',
                'before:'.$tglBatasAkhir,
                'after_or_equal:'.$tglbatasawal],
            'gambar.*' => 'image'
        ];
        $relatedRequests = [
            StoreUpahSupirRincianRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesZona_id,
                $rulesParent_id,
                $rulesTarif_id,
                $rulesKotaDari_id,
                $rulesKotaSampai_id
            );
        }
        
        return $rules;
    }
    
    public function attributes()
    {
        return [
            'kotadari' => 'kota dari',
            'kotasampai' => 'kota sampai',
            'statusaktif' => 'status aktif',
            'statusluarkota' => 'status luar kota',
            'tglmulaiberlaku' => 'tanggal mulai berlaku',
            'tglakhirberlaku' => 'tanggal akhir berlaku',
            'container.*' => 'container',
            'statuscontainer.*' => 'container',
            'nominalsupir.*' => 'nominal supir'
        ];
    }


}
