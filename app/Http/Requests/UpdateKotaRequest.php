<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Parameter;
use App\Rules\ExistZona;

class UpdateKotaRequest extends FormRequest
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

        $zona_id = $this->zona_id;
        $rulesZona_id = [];
        if ($zona_id != null && $zona_id !=0) {
            $rulesZona_id = [
                'zona_id' => ['required', 'numeric', 'min:1', new ExistZona()]
            ];
        } else if ($zona_id == null && $this->zona != '') {
            $rulesZona_id = [
                'zona_id' => ['required', 'numeric', 'min:1', new ExistZona()]
            ];
        }
        $rules =  [
            'kodekota' => ['required',Rule::unique('kota')->whereNotIn('id', [$this->id])],
            'keterangan' => 'nullable',
            'zona' => [],
            'statusaktif' => ['required', Rule::in($status)]
        ];

        $rule = array_merge(
            $rules,
            $rulesZona_id
        );

        return $rule;
    }

    public function attributes()
    {
        return [
            'kodekota' => 'kode kota',
            'statusaktif' => 'statusaktif'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodekota.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
