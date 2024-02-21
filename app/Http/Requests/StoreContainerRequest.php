<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
class StoreContainerRequest extends FormRequest
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
        return [
            'kodecontainer' => 'required|unique:container',
            'nominalsumbangan' => 'required|gt:0|numeric',
            'statusaktif' => ['required', Rule::in($status)],
        ];
    }

    public function attributes()
    {
        return [
            'kodecontainer' => 'Kode Container',
            'keterangan' => 'Keterangan',
            'statusaktif' => 'Status Aktif',
            'nominalsumbangan' => 'Nominal Sumbangan',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        
        return [
            'kodecontainer.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'nominalsumbangan.required' => ':attribute '. $controller->geterror('WI')->keterangan
          
        ];
    }
}
