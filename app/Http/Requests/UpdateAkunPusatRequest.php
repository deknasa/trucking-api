<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAkunPusatRequest extends FormRequest
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
            'keterangancoa' => 'required',
            'type' => 'required',
            'level' => 'required|int',
            'aktif' => 'required|int',
            'parent' => 'required',
            'statuscoa' => 'required|int',
            'statusaccountpayable' => 'required|int',
            'statusneraca' => 'required|int',
            'statuslabarugi' => 'required|int',
            'coamain' => 'required',
            'statusaktif' => 'required|int',
        ];
    }
}
