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
        if (request()->from == 'tas') {
            return [];
        }
        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }

        $subkelompok_id = $this->subkelompok_id;
        $rulesSubKelompok_id = [];
        if ($subkelompok_id != null) {
            if ($subkelompok_id == 0) {
                $rulesSubKelompok_id = [
                    'subkelompok_id' => ['required', 'numeric', 'min:1']
                ];
            } else {
                if ($this->subkelompok == '') {
                    $rulesSubKelompok_id = [
                        'subkelompok' => ['required']
                    ];
                }
            }
        } else if ($subkelompok_id == null && $this->subkelompok != '') {
            $rulesSubKelompok_id = [
                'subkelompok_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $rules = [
            'kodekategori' => ['required', 'unique:kategori'],
            'subkelompok' => 'required',
            // 'kategori' => 'required',
            'statusaktif' => ['required', Rule::in($status)]
        ];

        $rule = array_merge(
            $rules,
            $rulesSubKelompok_id
        );

        return $rule;
    }
    
    public function attributes()
    {
        return[
            'kodekategori' => 'kode kategori',
            'keterangan' => 'keterangan',
            'statusaktif' => 'status aktif'
        ];
    }


    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodekategori.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }    
}
