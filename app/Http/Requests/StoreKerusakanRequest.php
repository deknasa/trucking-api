<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreKerusakanRequest extends FormRequest
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
            'keterangan' => 'required|unique:kerusakan',
            'statusaktifnama' => ['required'],
        ];

        $rules = array_merge(
            $rules,
            $rulesStatusAktif,
        );

        return $rules;
    }
}
