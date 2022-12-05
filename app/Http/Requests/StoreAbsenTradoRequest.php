<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class StoreAbsenTradoRequest extends FormRequest
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
            'kodeabsen' => 'required',
            'keterangan' => 'required',
            'statusaktif' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'kodeabsen' => 'kode absen',
            'statusaktif' => 'status',
        ];
    }

}
