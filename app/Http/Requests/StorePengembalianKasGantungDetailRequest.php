<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\CekMaxBayarPengembalianKasgantung;
use App\Rules\CekMinusSisaPengembalianKasgantung;
use App\Rules\KeteranganInput;
use App\Rules\PreventInputType;
use Illuminate\Foundation\Http\FormRequest;

class StorePengembalianKasGantungDetailRequest extends FormRequest
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
            'nominal.*' => ['required','numeric','gt:0', new CekMaxBayarPengembalianKasgantung()],
            'keterangandetail.*' => 'required',
            'sisa.*' => ['required','numeric','min:0', new CekMinusSisaPengembalianKasgantung()],
        ];
    }

    public function attributes()
    {
        return [
            'nominal' => 'nominal',
            'keterangandetail' => 'keterangan',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'keterangandetail.required' => ':attribute ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
