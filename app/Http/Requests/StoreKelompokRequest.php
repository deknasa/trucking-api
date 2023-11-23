<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreKelompokRequest extends FormRequest
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

        $rules = [
            'kodekelompok' => ['required', 'unique:kelompok'],
            'keterangan' => ['required'],
            'statusaktif' => ['required', Rule::in($status)]
        ];

        return $rules;
    }
    
    public function attributes()
    {
        return[
            'kodekelompok' => 'kode kelompok',
            'statusaktif' => 'status aktif',
            'keterangan' => 'keterangan',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodekelompok.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }

}
