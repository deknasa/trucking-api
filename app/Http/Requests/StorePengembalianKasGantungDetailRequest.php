<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
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

            'keterangandetail' => 'required|array',
            'keterangandetail.*' => ['required', new KeteranganInput()],
            'coadetail' => 'required|array',
            'coadetail.*' => ['required',new PreventInputType()],
        ];
    }

    public function attributes()
    {
        return [
            'coadetail' => 'kode perkiraan',
            'keterangandetail' => 'keterangan',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'coadetail.required' => ':attribute ' . $controller->geterror('WI')->keterangan,
            'keterangandetail.required' => ':attribute ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
