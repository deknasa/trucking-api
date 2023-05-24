<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;


class StoreSubKelompokRequest extends FormRequest
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

        if ($this->kelompok=='' and $this->kelompok_id=='') {
            return [
                'kodesubkelompok' => 'required',
                'kelompok' => 'required',
                'statusaktif' => 'required|numeric',
            ];
        } else {
            return [
                'kodesubkelompok' => 'required',
                'kelompok' => 'required',
                'kelompok_id' => 'required',
                'statusaktif' => 'required|numeric',
            ];
        }

    
        
    }

    public function attributes()
    {
        return [
            'kodesubkelompok' => 'kode subkelompok',
            'kelompok' => 'kelompok',
            'kelompok_id' => 'kelompok ',
            'keterangan' => 'keterangan',
            'statusaktif' => 'status aktif',
        ];
    }

  
}
