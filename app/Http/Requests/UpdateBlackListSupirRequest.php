<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class UpdateBlackListSupirRequest extends FormRequest
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
            // "namasupir"=> "required",
            'noktp' => ['required','min:16','max:16','unique:blacklistsupir,noktp,'.$this->id],
            'nosim' => ['min:12','max:12','unique:blacklistsupir,nosim,'.$this->id],
        ];
    }

    public function attributes()
    {
        return [
            'namasupir' => 'Nama Supir',
            'noktp' => 'No KTP',
            'nosim' => 'No SIM',
        ];
    }

    public function messages() 
    {
        $controller = new ErrorController;

        return [
            'noktp.max' => 'Max. 16 karakter',
            'noktp.min' => 'Min. 16 karakter',
            'noktp.unique' => ':attribute' . ' ' . $controller->geterror('SPI')->keterangan,
            'nosim.max' => 'Max. 12 karakter',
            'nosim.min' => 'Min. 12 karakter',
            'nosim.unique' => ':attribute' . ' ' . $controller->geterror('SPI')->keterangan,
        ];
    }
}
