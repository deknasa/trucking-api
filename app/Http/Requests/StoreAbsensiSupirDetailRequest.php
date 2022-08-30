<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsensiSupirDetailRequest extends FormRequest
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
            'trado_id' => 'required|array',
            'trado_id.*' => 'required|int|exists:trado,id',
            'supir_id' => 'required|array',
            'supir_id.*' => 'required|int|exists:supir,id',
            'uangjalan' => 'required|array',
            'uangjalan.*' => 'required|numeric',
            'absen_id' => 'required|array',
            'absen_id.*' => 'required|int|exists:absentrado,id',
            'jam' => 'required|array',
            'jam.*' => 'required',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required',
        ];
    }
}
