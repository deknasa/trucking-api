<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;

class UpdateCabangRequest extends FormRequest
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
            'kodecabang' => ['required',Rule::unique('cabang')->whereNotIn('id', [$this->id])],
            'namacabang' => ['required',Rule::unique('cabang')->whereNotIn('id', [$this->id])],
            'statusaktif' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'kodecabang' => 'kode cabang',
            'namacabang' => 'nama cabang',
            'statusaktif' => 'status',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'kodecabang.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'namacabang.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,

        ];
    }
}
