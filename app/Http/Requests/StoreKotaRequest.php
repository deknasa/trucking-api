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

        $zona_id = $this->zona_id;
        $rulesZona_id = [];
        if ($zona_id != null) {
            if ($zona_id == 0) {
                $rulesZona_id = [
                    'zona_id' => [ 'numeric', 'min:1']
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
                'zona_id' => [ 'numeric', 'min:1']
            ];
        }

        $rules = [
            'kodekota' => ['required','unique:kota'],
            'keterangan' => ['nullable'],
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
