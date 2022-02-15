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
            'namatabel.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'postingdari.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'idtrans.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'nobuktitrans.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'aksi.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'datajson.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'modifiedby.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,

        ];
    }
}
