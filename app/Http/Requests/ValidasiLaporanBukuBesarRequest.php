<?php

namespace App\Http\Requests;

use App\Rules\ExistAkunPusat;
use App\Rules\ExistAkunPusatId;
use Illuminate\Foundation\Http\FormRequest;

class ValidasiLaporanBukuBesarRequest extends FormRequest
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
        $coadari_id = $this->coadari_id;
        $rulesCoaDari = [];
        if ($coadari_id != null) {
            $rulesCoaDari = [
                'coadari_id' => ['required', 'numeric', 'min:1', new ExistAkunPusatId()],
            ];
        } else if ($coadari_id == null && $this->coadari != '') {
            $rulesCoaDari = [
                'coadari_id' => ['required', 'numeric', 'min:1', new ExistAkunPusatId()],
            ];
        }

        $coasampai_id = $this->coasampai_id;
        $rulesCoaSampai = [];
        if ($coasampai_id != null) {
            $rulesCoaSampai = [
                'coasampai_id' => ['required', 'numeric', 'min:1', new ExistAkunPusatId()],
            ];
        } else if ($coasampai_id == null && $this->coasampai != '') {
            $rulesCoaSampai = [
                'coasampai_id' => ['required', 'numeric', 'min:1', new ExistAkunPusatId()],
            ];
        }
        $rule = [
            'dari' => [
                'required', 'date_format:d-m-Y',
            ],
            'sampai' => [
                'required', 'date_format:d-m-Y',
                'after_or_equal:' . request()->dari
            ],
            'coadari' => ['required'],
            'coasampai' => ['required'],
        ];

        $rule = array_merge(
            $rule,
            $rulesCoaDari,
            $rulesCoaSampai
        );

        return $rule;
    }
}
