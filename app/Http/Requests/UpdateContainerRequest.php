<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;
use App\Models\Parameter;


class UpdateContainerRequest extends FormRequest
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
            'kodecontainer' => ['required',Rule::unique('container')->whereNotIn('id', [$this->id])],
            'nominalsumbangan' => 'required|gt:0|numeric',
            'statusaktif' => ['required', Rule::in($status)]
        ];
    }

    public function attributes()
    {
        return [
            'keterangan' => 'Keterangan',
            'nominalsumbangan' => 'Nominal Sumbangan',
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
