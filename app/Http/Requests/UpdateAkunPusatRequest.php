<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

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
            'coa' => 'required|unique:akunpusat,coa',
            'keterangancoa' => 'required',
            'type' => 'required',
            'level' => 'required|int',
            'parent' => 'required',
            'statuscoa' => 'required|int',
            'statusaccountpayable' => 'required|int',
            'statusneraca' => 'required|int',
            'statuslabarugi' => 'required|int',
            'coamain' => 'required',
            'statusaktif' => 'required|int',
        ];
    }

    public function attributes()
    {
        return [
            'coa' => 'kode cabang',
            'keterangancoa' => 'keteragn coa',
            'type' => 'type',
            'level' => 'status aktif',
            'parent' => 'parent',
            'statuscoa' => 'status coa',
            'statusaccountpayable' => 'status account payable',
            'statusneraca' => 'status neraca',
            'statuslabarugi' => 'status laba rugi',
            'coamain' => 'kode perkiraan utama',
            'statusaktif' => 'status aktif',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'coa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'keterangancoa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'type.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'level.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'parent.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statuscoa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaccountpayable.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusneraca.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statuslabarugi.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coamain.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
