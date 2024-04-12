<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreQtyTambahGantiOliRequest extends FormRequest
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

        $data = $parameter->getcombodata('STATUS OLI', 'STATUS OLI');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statusoli[] = $item['id'];
        }       
        
        $data = $parameter->getcombodata('STATUS SERVICE RUTIN', 'STATUS SERVICE RUTIN');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statusservicerutin[] = $item['id'];
        }        
        
        return [
            'qty' => 'required|gt:0|numeric',
            'statusaktif' => ['required', Rule::in($status)],
            'statusoli' => ['required', Rule::in($statusoli)],
            'statusservicerutin' => ['required', Rule::in($statusservicerutin)],
        ];
    }

    public function attributes()
    {
        return [
            'keterangan' => 'Keterangan',
            'statusaktif' => 'Status Aktif',
            'statusoli' => 'Status Oli',
            'statusservicerutin' => 'Status Service Rutin',
            'qty' => 'Qty',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        
        return [
            'statusaktif.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'statusoli.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'statusservicerutin.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'qty.required' => ':attribute '. $controller->geterror('WI')->keterangan
          
        ];
    }
}
