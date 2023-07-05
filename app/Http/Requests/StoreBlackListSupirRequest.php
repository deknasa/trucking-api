<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreBlackListSupirRequest extends FormRequest
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
            'noktp' => ['required','unique:blacklistsupir','min:16','max:16'],
            'nosim' => ['unique:blacklistsupir','min:12','min:12'],
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
