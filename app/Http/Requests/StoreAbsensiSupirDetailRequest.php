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
            'trado' => 'required|array',
            'trado.*' => 'required',
            // 'supir' => 'required|array',
            // 'supir.*' => 'required',
            // 'uangjalan' => 'required|array',
            // 'uangjalan.*' => 'required|numeric|gt:0',
            // // 'absen' => 'required|array',
            // // 'absen.*' => 'required',
            // 'jam' => 'required|array',
            // 'jam.*' => 'required',
            // 'keterangan_detail' => 'required|array',
            // 'keterangan_detail.*' => 'required',
        ];
    }

    
}
