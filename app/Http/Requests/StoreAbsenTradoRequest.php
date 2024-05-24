<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreAbsenTradoRequest extends FormRequest
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
        $dataAktif = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $dataAktif = json_decode($dataAktif, true);
        foreach ($dataAktif as $item) {
            $statusAktif[] = $item['id'];
        }

        $rules = [
            "kodeabsen" => ['required','unique:absentrado'],
            "statusaktif" => ['required', Rule::in($statusAktif)],
            'key' => 'required',
            'value' => 'required',
            'key.*' => ['required'],
            'value.*' => ['required']
        ];

        return $rules;
    }

    public function attributes()
    {
        return [
            'kodeabsen' => 'kode absen',
            'statusaktif' => 'status',
            'key' => 'judul',
            'value' => 'keterangan',
            'key.*' => 'judul',
            'value.*' => 'keterangan',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodeabsen.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan
        ];
    }  

}
