<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class UpdateLogTrailRequest extends FormRequest
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
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'namatabel.required' => 'namatabel '. $controller->geterror(1)->keterangan,
            'postingdari.required' => 'postingdari '. $controller->geterror(1)->keterangan,
            'idtrans.required' => 'idtrans '. $controller->geterror(1)->keterangan,
            'nobuktitrans.required' => 'nobuktitrans '. $controller->geterror(1)->keterangan,
            'aksi.required' => 'aksi '. $controller->geterror(1)->keterangan,
            'datajson.required' => 'datajson '. $controller->geterror(1)->keterangan,
            'modifiedby.required' => 'modifiedby '. $controller->geterror(1)->keterangan,

        ];
    }
}
