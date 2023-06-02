<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;
use App\Models\Parameter;

use App\Models\Trado;
use App\Rules\ValidasiDestroyTrado ;

class DestroyTradoRequest extends FormRequest
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
            'kodetrado' => new ValidasiDestroyTrado(),
        ];
    }

    public function attributes()
    {
        return [
            'keterangan' => 'Keterangan',
            'statusaktif' => 'Status Aktif'
        ];
    }

    // public function messages()
    // {
    //     $controller = new ContainerController;
        
    //     return [
    //         'statusaktif.required' => ':attribute '. $controller->geterror('WI')->keterangan,
    //     ];
    // }
}
