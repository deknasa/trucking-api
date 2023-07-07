<?php

namespace App\Http\Requests;

use App\Rules\ExistPelangganId;
use App\Rules\ExistSupplierId;
use Illuminate\Foundation\Http\FormRequest;

class ValidasiLaporanKartuPiutangPerPelangganRequest extends FormRequest
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
        $pelanggandari_id = $this->pelanggandari_id;
        $rulespelanggandari = [];
        if ($pelanggandari_id != null) {
            $rulespelanggandari = [
                'pelanggandari_id' => ['required', 'numeric', 'min:1', new ExistPelangganId()],
            ];
        } else if ($pelanggandari_id == null && $this->pelanggandari != '') {
            $rulespelanggandari = [
                'pelanggandari_id' => ['required', 'numeric', 'min:1', new ExistPelangganId()],
            ];
        }

        $pelanggansampai_id = $this->pelanggansampai_id;
        $rulespelanggansampai = [];
        if ($pelanggansampai_id != null) {
            $rulespelanggansampai = [
                'pelanggansampai_id' => ['required', 'numeric', 'min:1', new ExistPelangganId()],
            ];
        } else if ($pelanggansampai_id == null && $this->pelanggansampai != '') {
            $rulespelanggansampai = [
                'pelanggansampai_id' => ['required', 'numeric', 'min:1', new ExistPelangganId()],
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
            'pelanggandari' => ['required'],
            'pelanggansampai' => ['required'],
        ];

        $rule = array_merge(
            $rule,
            $rulespelanggandari,
            $rulespelanggansampai
        );

        return $rule;
    }
}
