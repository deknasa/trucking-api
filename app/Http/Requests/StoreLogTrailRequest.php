<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLogTrailRequest extends FormRequest
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
            'modifiedby' => 'modifiedby',
        ];
    }
}
