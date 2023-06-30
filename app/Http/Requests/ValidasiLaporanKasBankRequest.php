<?php

namespace App\Http\Requests;

use App\Rules\ExistBank;
use Illuminate\Foundation\Http\FormRequest;

class ValidasiLaporanKasBankRequest extends FormRequest
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
        return [
            'dari' => [
                'required', 'date_format:d-m-Y',
            ],
            'sampai' => [
                'required', 'date_format:d-m-Y',
                'after_or_equal:' . request()->dari
            ],
            'bank' => ['required', new ExistBank()]
        ];
    }
}
