<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreTypeAkuntansiRequest extends FormRequest
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
        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }
        return [
            'kodetype' => 'required|unique:typeakuntansi',
            'order' => ['required','gt:0','numeric', 'max:9999'], 
            'akuntansi_id' => 'required',
            
            'statusaktif' => ['required', Rule::in($status)],
        ];
    }

    public function attributes()
    {
        return [
            'kodetype' => 'Kode Tipe',
            'statusaktif' => 'Status Aktif',
            'order' => 'Order',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        
        return [
            'kodetype.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute '. $controller->geterror('WI')->keterangan,
            'order.required' => ':attribute '. $controller->geterror('WI')->keterangan
          
        ];
    }
}
