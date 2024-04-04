<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\NotInKarakter_;
use Illuminate\Validation\Rule;

class StorePenerimaRequest extends FormRequest
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

        $data1 = $parameter->getcombodata('STATUS KARYAWAN', 'STATUS KARYAWAN');
        $data1 = json_decode($data1, true);
        foreach ($data1 as $item1) {
            $statusKaryawan[] = $item1['id'];
        }
        
        return [
            'namapenerima' => 'required',
            'keterangan' => 'required',
            'npwp' => [new NotInKarakter_(),'unique:penerima'],
            'noktp' => [new NotInKarakter_(),'unique:penerima'],
            'statusaktif' => ['required', Rule::in($status),'numeric', 'min:1'],
            'statuskaryawan' => ['required', Rule::in($statusKaryawan),'numeric', 'min:1'],
        ];
    }

    public function attributes()
    {
        return [
            'namapenerima' => 'nama penerima',
            'npwp' => 'npwp',
            'noktp' => 'noktp',
            'statusaktif' => 'status aktif',
            'statuskaryawan' => 'status karyawan',
        ];
    }

    
    public function messages()
    {
        $controller = new ErrorController;

        return [
            'namapenerima.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'npwp.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'noktp.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statuskaryawan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,         
        ];
    }  
}
