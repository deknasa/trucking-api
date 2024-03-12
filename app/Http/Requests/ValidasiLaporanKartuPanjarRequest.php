<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistAgenId;

class ValidasiLaporanKartuPanjarRequest extends FormRequest
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
        $agendari_id = $this->agendari_id;
        $rulesAgenDari = [];
        if ($agendari_id != null) {
            $rulesAgenDari = [
                'agendari_id' => ['required', 'numeric', 'min:1', new ExistAgenId()],
            ];
        } else if ($agendari_id == null && $this->agendari != '') {
            $rulesAgenDari = [
                'agendari_id' => ['required', 'numeric', 'min:1', new ExistAgenId()],
            ];
        }

        $agensampai_id = $this->agensampai_id;
        $rulesAgenSampai = [];
        if ($agensampai_id != null) {
            $rulesAgenSampai = [
                'agensampai_id' => ['required', 'numeric', 'min:1', new ExistAgenId()],
            ];
        } else if ($agensampai_id == null && $this->agensampai != '') {
            $rulesAgenSampai = [
                'agensampai_id' => ['required', 'numeric', 'min:1', new ExistAgenId()],
            ];
        }

        $rule =  [
            'dari' => [
                'required', 'date_format:d-m-Y',
            ],
            
            // 'agendari' => ['required'],
            // 'agensampai' => ['required']
        ];

        $rule = array_merge(
            $rule,
            // $rulesAgenDari,
            // $rulesAgenSampai
        );

        return $rule;
    }
}
