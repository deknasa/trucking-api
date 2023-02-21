<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MandorAbsensiSupirEditSupirValidasiTrado ;

class UpdateMandorAbsensiSupirRequest extends FormRequest
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
            'trado' => 'required',
            'trado_id' => 'required',
            'supir' => 'required',
            'supir_id' => ['required',new MandorAbsensiSupirEditSupirValidasiTrado()],
            'jam' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'supir_id' => 'supir',
        ];
    }
}
