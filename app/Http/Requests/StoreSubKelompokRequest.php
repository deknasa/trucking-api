<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;


class StoreSubKelompokRequest extends FormRequest
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

        $kelompok_id = $this->kelompok_id;
        $rulesKelompok_id = [];
        if ($kelompok_id != null) {
            if ($kelompok_id == 0) {
                $rulesKelompok_id = [
                    'kelompok_id' => ['required', 'numeric', 'min:1']
                ];
            } else {
                if ($this->kelompok == '') {
                    $rulesKelompok_id = [
                        'kelompok' => ['required']
                    ];
                }
            }
        } else if ($kelompok_id == null && $this->kelompok != '') {
            $rulesKelompok_id = [
                'kelompok_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $rules = [
            'kodesubkelompok' => ['required','unique:subkelompok'],
            'keterangan' => ['nullable'],
            'kelompok' => ['required'],
            'statusaktif' => ['required', Rule::in($status)]
        ];

        $rule = array_merge(
            $rules,
            $rulesKelompok_id
        );
        
        return $rule;
        
    }

    public function attributes()
    {
        return [
            'kodesubkelompok' => 'kode subkelompok',
            'keterangan' => 'keterangan',
            'statusaktif' => 'status aktif',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodesubkelompok.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }



  
}
