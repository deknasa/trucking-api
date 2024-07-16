<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreKotaRequest extends FormRequest
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

        $zona_id = $this->zona_id;
        $rulesZona_id = [];
        if ($zona_id != null) {
            if ($zona_id == 0) {
                $rulesZona_id = [
                    'zona_id' => ['numeric', 'min:1']
                ];
            } else {
                if ($this->zona == '') {
                    $rulesZona_id = [
                        'zona' => []
                    ];
                }
            }
        } else if ($zona_id == null && $this->zona != '') {
            $rulesZona_id = [
                'zona_id' => ['numeric', 'min:1']
            ];
        }

        $rules = [
            'kodekota' => ['required', 'unique:kota'],
            'keterangan' => ['nullable'],
            'zona' => [],
            'statusaktifnama' => ['required'],
        ];

        $rule = array_merge(
            $rules,
            $rulesZona_id,
            $rulesStatusAktif
        );

        return $rule;
    }

    public function attributes()
    {
        return [
            'kodekota' => 'kode kota',
            'statusaktifnama' => 'statusaktif'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodekota.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktifnama.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
