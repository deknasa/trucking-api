<?php

namespace App\Http\Requests;

use App\Rules\ExistStokId;
use Illuminate\Foundation\Http\FormRequest;

class ValidasiLaporanPembelianStokRequest extends FormRequest
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
        $stokdari_id = $this->stokdari_id;
        $rulesstokdari = [];
        if ($stokdari_id != null) {
            $rulesstokdari = [
                'stokdari_id' => ['required', 'numeric', 'min:1', new ExistStokId()],
            ];
        } else if ($stokdari_id == null && $this->pelanggandari != '') {
            $rulesstokdari = [
                'stokdari_id' => ['required', 'numeric', 'min:1', new ExistStokId()],
            ];
        }

        $stoksampai_id = $this->stoksampai_id;
        $rulesstoksampai = [];
        if ($stoksampai_id != null) {
            $rulesstoksampai = [
                'stoksampai_id' => ['required', 'numeric', 'min:1', new ExistStokId()],
            ];
        } else if ($stoksampai_id == null && $this->pelanggansampai != '') {
            $rulesstoksampai = [
                'stoksampai_id' => ['required', 'numeric', 'min:1', new ExistStokId()],
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
            'stokdari' => ['required'],
            'stoksampai' => ['required'],
        ];

        $rule = array_merge(
            $rule,
            $rulesstokdari,
            $rulesstoksampai
        );

        return $rule;
    }
}
