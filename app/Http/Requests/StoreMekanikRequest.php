<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMekanikRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
<<<<<<< HEAD
        return false;
=======
        return true;
>>>>>>> 45bc0d5a7d263f6ec185c4c06e9fc88025a55e7c
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
<<<<<<< HEAD
            //
=======
            'namamekanik' => 'required',
            'keterangan' => 'required',
            'statusaktif' => 'required',
>>>>>>> 45bc0d5a7d263f6ec185c4c06e9fc88025a55e7c
        ];
    }
}
