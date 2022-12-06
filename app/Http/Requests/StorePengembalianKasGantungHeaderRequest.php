<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengembalianKasGantungHeaderRequest extends FormRequest
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
            "pelanggan_id" => "required",
            "pelanggan" => "required",
            "bank_id" => "required",
            "bank" => "required",
            "keterangan" => "required",
            "coa" => "required",
            // "statusformat" => "required",
            // "statushitungstok" => "required"
        ];
    }
}
