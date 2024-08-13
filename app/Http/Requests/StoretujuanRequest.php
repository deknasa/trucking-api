<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreTujuanRequest extends FormRequest
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
        $statusaktif = $this->statusaktif;
        $rulesStatusAktif = [];
        if ($statusaktif != null) {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($status)]
            ];
        } else if ($statusaktif == null && $this->statusaktifnama != '') {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($status)]
            ];
        }

        $rules = [
            'tujuan' => ['required', 'unique:tujuan,kodetujuan'],
            'statusaktifnama' => ['required'],
        ];

        $rules = array_merge(
            $rules,
            $rulesStatusAktif,
        );

        return $rules;
    }

    public function attributes()
    {
        return [
            'tujuan' => 'Tujuan',
            'statusaktifnama' => 'status',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'tujuan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktifnama.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
