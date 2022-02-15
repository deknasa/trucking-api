<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class DestroyLogTrailRequest extends FormRequest
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
            'namatabel' => 'required',
            'postingdari' => 'required',
            'idtrans' => 'required',
            'nobuktitrans' => 'required',
            'aksi' => 'required',
            'datajson' => 'required',
            'modifiedby' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'namatabel' => 'namatabel',
            'postingdari' => 'postingdari',
            'idtrans' => 'idtrans',
            'nobuktitrans' => 'nobuktitrans',
            'aksi' => 'aksi',
            'datajson' => 'datajson',
            'modifiedby' => 'modified by',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'namatabel.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'postingdari.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'idtrans.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'nobuktitrans.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'aksi.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'datajson.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,
            'modifiedby.required' => ':attribute'.' '. $controller->geterror('WI')->keterangan,

        ];
    }
}
