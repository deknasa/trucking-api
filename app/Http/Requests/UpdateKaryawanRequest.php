<?php

namespace App\Http\Requests;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKaryawanRequest extends FormRequest
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

        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }

        $data = $parameter->getcombodata('STATUS STAFF', 'STATUS STAFF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statusstaff[] = $item['id'];
        }

        $rules = [
            'namakaryawan' =>  ['required',Rule::unique('karyawan')->whereNotIn('id', [$this->id])],
            'jabatan' => ['required'],
            'statusaktif' => ['required', Rule::in($status)],
            'statusstaff' => ['required', Rule::in($statusstaff)]
        ];

        return $rules;

    
    }

    public function attributes()
    {
        return [
          'namakaryawan' => 'Nama Karyawan',
          'statusaktif' => 'status',
          'statusstaff' => 'status staff'
        ];
    }
}
