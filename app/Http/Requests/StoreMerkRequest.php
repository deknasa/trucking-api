<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreMerkRequest extends FormRequest
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
        if (request()->from == 'tas') {
            return [];
        }
        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }
        
        $rules = [
            'kodemerk' => ['required', 'unique:merk'],
            // 'keterangan' => ['required'],
            'statusaktif' => ['required', Rule::in($status)]
        ];

        return $rules;
    }
    
    public function attributes()
    {
        return [
            'kodemerk' => 'kode merk',
            'keterangan' => 'keterangan',
            'statusaktif' => 'statusaktif'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodemerk.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
