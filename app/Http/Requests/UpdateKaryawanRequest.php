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
        if (request()->from == 'tas') {
            return [];
        } 
        

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

        $statusstaff = $this->statusstaff;
        $rulesStatusStaff = [];
        if ($statusstaff != null) {
            $rulesStatusStaff = [
                'statusstaff' => ['required', Rule::in($statusstaff)]
            ];
        } else if ($statusstaff == null && $this->statusstaffnama != '') {
            $rulesStatusStaff = [
                'statusstaff' => ['required', Rule::in($statusstaff)]
            ];
        }

        $rules = [
            'namakaryawan' =>  ['required', Rule::unique('karyawan')->whereNotIn('id', [$this->id])],
            'jabatan' => ['required'],
            'statusstaffnama' => ['required'],
            'statusaktifnama' => ['required'],
        ];

        $rules = array_merge(
            $rules,
            $rulesStatusAktif,
            $rulesStatusStaff
        );
        return $rules;
    }

    public function attributes()
    {
        return [
            'namakaryawan' => 'Nama Karyawan',
            'statusaktifnama' => 'status',
            'statusstaffnama' => 'status staff'
        ];
    }
}
