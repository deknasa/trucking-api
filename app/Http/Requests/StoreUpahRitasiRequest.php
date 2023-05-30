<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\UniqueUpahRitasiSampai;
use Illuminate\Validation\Rule;

class StoreUpahRitasiRequest extends FormRequest
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
        $kotadari_id = $this->kotadari_id;
        $rulesKotaDari_id = [];
        if ($kotadari_id != null) {
            if ($kotadari_id == 0) {
                $rulesKotaDari_id = [
                    'kotadari_id' => ['required', 'numeric', 'min:1']
                ];
            } 
        } else if ($kotadari_id == null && $this->kotadari != '') {
            $rulesKotaDari_id = [
                'kotadari_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $kotasampai_id = $this->kotasampai_id;
        $rulesKotaSampai_id = [];
        if ($kotasampai_id != null) {
            if ($kotasampai_id == 0) {
                $rulesKotaSampai_id = [
                    'kotasampai_id' => ['required', 'numeric', 'min:1', new UniqueUpahRitasiSampai()]
                ];
            } 
        } else if ($kotasampai_id == null && $this->kotasampai != '') {
            $rulesKotaSampai_id = [
                'kotasampai_id' => ['required', 'numeric', 'min:1', new UniqueUpahRitasiSampai()]
            ];
        }

        $parameter = new Parameter();
        $dataAktif = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $dataAktif = json_decode($dataAktif, true);
        foreach ($dataAktif as $item) {
            $statusAktif[] = $item['id'];
        }

        $tglBatasAkhir = (date('Y') + 1) . '-01-01';
        $rules =  [
            'kotadari' => 'required',
            'kotasampai' => ['required', new UniqueUpahRitasiSampai()],
            'jarak' => ['required', 'numeric', 'gt:0', 'max:' . (new ParameterController)->getparamid('BATAS NILAI JARAK', 'BATAS NILAI JARAK')->text],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'tglmulaiberlaku' => ['required', 'date_format:d-m-Y',
                'before:'.$tglBatasAkhir,
                'after_or_equal:'.date('d-m-Y')
            ],
        ];

        $relatedRequests = [
            StoreUpahRitasiRincianRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesKotaDari_id,
                $rulesKotaSampai_id
            );
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'kotadari' => 'dari',
            'kotasampai' => 'tujuan',
            'kotadari_id' => 'dari',
            'kotasampai_id' => 'tujuan',
            'statusaktif' => 'status aktif',
            'tglmulaiberlaku' => 'tanggal mulai berlaku',
            'container.*' => 'container',
            'nominalsupir.*' => 'nominal supir',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'jarak.max' => ':attribute ' . 'maximal jarak '. (new ParameterController)->getparamid('BATAS NILAI JARAK','BATAS NILAI JARAK')->text,
            'jarak.gt' => ':attribute ' . (new ErrorController)->geterror('GT-ANGKA-0')->keterangan,
            'kotadari_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'kotasampai_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
        ];
    }
}
