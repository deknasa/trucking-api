<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreKategoriRequest extends FormRequest
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
            'kodekategori' => ['required', 'unique:kategori'],
            'subkelompok' => 'required',
            'statusaktif' => ['required', Rule::in($status)]
        ];

        return $rules;
    }
    
    public function attributes()
    {
        return[
            'kodekategori' => 'kode kategori',
            'subkelompok' => 'sub kelompok',
            'keterangan' => 'keterangan',
            'statusaktif' => 'status aktif'
        ];
    }


    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodekategori.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'subkelompok.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }    
}
