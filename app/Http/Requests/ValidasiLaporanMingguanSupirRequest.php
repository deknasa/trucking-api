<?php

namespace App\Http\Requests;

use App\Rules\ExistBank;
use App\Rules\ExistTrado;
use App\Rules\ExistTradoId;
use Illuminate\Foundation\Http\FormRequest;

class ValidasiLaporanMingguanSupirRequest extends FormRequest
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
        $tradodari_id = $this->tradodari_id;
        $rulestradodari = [];
        if ($tradodari_id != null) {
            $rulestradodari = [
                'tradodari_id' => ['required', 'numeric', 'min:1', new ExistTradoId()],
            ];
        } else if ($tradodari_id == null && $this->tradodari != '') {
            $rulestradodari = [
                'tradodari_id' => ['required', 'numeric', 'min:1', new ExistTradoId()],
            ];
        }

        $tradosampai_id = $this->tradosampai_id;
        $rulestradosampai = [];
        if ($tradosampai_id != null) {
            $rulestradosampai = [
                'tradosampai_id' => ['required', 'numeric', 'min:1', new ExistTradoId()],
            ];
        } else if ($tradosampai_id == null && $this->tradosampai != '') {
            $rulestradosampai = [
                'tradosampai_id' => ['required', 'numeric', 'min:1', new ExistTradoId()],
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
            'tradodari' => ['required'],
            'tradosampai' => ['required'],
        ];

        $rule = array_merge(
            $rule,
            $rulestradodari,
            $rulestradosampai
        );

        return $rule;
    }
}
