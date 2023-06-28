<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;

class UpdateUpahRitasiRincianRequest extends FormRequest
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
            'container.*' => 'required',
            'container_id.*' => 'required',
            'liter.*' => ['required','numeric','min:0','max:'. (new ParameterController)->getparamid('BATAS NILAI LITER','BATAS NILAI LITER')->text],
        ];
    }

    public function attributes()
    {
        return [
            'liter.*' => 'liter',
        ];
    }
}
