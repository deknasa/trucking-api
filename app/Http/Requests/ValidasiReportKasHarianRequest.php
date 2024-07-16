<?php

namespace App\Http\Requests;

use App\Rules\ExistBank;
use Illuminate\Foundation\Http\FormRequest;

class ValidasiReportKasHarianRequest extends FormRequest
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
            'periode' => [
                'required', 'date_format:m-Y',
            ],
            'bank' => ['required', new ExistBank()]
        ];
    }
}
