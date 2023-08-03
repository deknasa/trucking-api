<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetRicRequest extends FormRequest
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
        
        // First day of the month.
        $awalPeriode = date('Y-m-01');
        return [
            'tgldari' => ['required', 'date_format:d-m-Y', 'before_or_equal:' . date('Y-m-d')],
            'tglsampai' => ['required', 'date_format:d-m-Y', 'before_or_equal:' . date('Y-m-d'), 'after_or_equal:' . date('Y-m-d', strtotime($this->tgldari))],

        ];
    }
}
