<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTripTangkiRequest extends FormRequest
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
            'kodetangki' => ['required', Rule::unique('triptangki')->whereNotIn('id', [$this->id])],
            'keterangan' => 'required',
            'statusaktif' => ['required', Rule::in($status)]
        ];
    }
    public function attributes()
    {
        return [
            'kodetangki' => 'kode tangki',
            'statusaktif' => 'Status Aktif'
        ];
    }
}
