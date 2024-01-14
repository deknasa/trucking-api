<?php

namespace App\Http\Requests;

use App\Rules\NominalInvoiceLunasPusat;
use Illuminate\Foundation\Http\FormRequest;

class StoreinvoicelunaskepusatRequest extends FormRequest
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
            'bayar' => 'required|gt:0|numeric',
            'potongan' => 'min:0|numeric',
            'sisa'=> new NominalInvoiceLunasPusat()
        ];
    }

    public function attributes()
    {
        return[
            'potongan' => 'nk'
        ];
    }
}
