<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanTripInapRequest extends FormRequest
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
            "absensi_id" => ["required"],
            "tglabsensi" => ["required"],
            "trado_id" => ["required"],
            "trado" => ["required"],
        ];
    }

    public function attributes()
    {
        return [
            "absensi_id" => "tgl absensi",
            "tglabsensi" => "tgl absensi",
            "trado_id" => "trado_id",
        ];
    }
}
