<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\ParameterController;
use App\Rules\ExistCabang;
use Illuminate\Support\Facades\DB;

class StoreUserRequest extends FormRequest
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
        $data = $parameter->getcombodata('STATUS AKSES', 'STATUS AKSES');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statusAkses[] = $item['id'];
        }
        
        $cabang_id = $this->cabang_id;
        $rulesCabang_id = [];
        if ($cabang_id != null) {
            $rulesCabang_id = [
                'cabang_id' => ['required', 'numeric', 'min:1', new ExistCabang()]
            ];
        } else if ($cabang_id == null && $this->cabang != '') {
            $rulesCabang_id = [
                'cabang_id' => ['required', 'numeric', 'min:1', new ExistCabang()]
            ];
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
        
        $statusakses = $this->statusakses;
        $rulesStatusAkses = [];
        if ($statusakses != null) {
            $rulesStatusAkses = [
                'statusakses' => ['required', Rule::in($statusAkses)]
            ];
        } else if ($statusakses == null && $this->statusaksesnama != '') {
            $rulesStatusAkses = [
                'statusakses' => ['required', Rule::in($statusAkses)]
            ];
        }
        $rules = [
         
            'user' => ['required', 'unique:user,user'],
            'name' => 'required|unique:user',
            'email' => 'required|unique:user|email:rfc,dns',
            // 'password' => 'required',
            // 'karyawan_id' => 'required',
            'cabang' => 'required',
            // 'dashboard' => 'required',
            // 'statusaktif' => ['required', 'int', 'exists:parameter,id'],
            'statusaksesnama' => ['required'],
            'statusaktifnama' => ['required'],
        ];
        $rules = array_merge(
            $rules,
            $rulesCabang_id,
            $rulesStatusAktif,
            $rulesStatusAkses
        );
        return $rules;
        
    }

    public function attributes()
    {
        return [
            'user' => 'user',
            'name' => 'nama user',
            'email' => 'email',
            'password' => 'password',
            'karyawan_id' => 'karyawan',
            'dashboard' => 'dashboard',
            'statusaktif' => 'status',
            'statusakses' => 'status akses',
            'statusaksesnama' => 'status akses',
            'statusaktifnama' => 'status',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'email.required' => ':attribute' . ' ' . $controller->geterror('EMAIL')->keterangan,
        ];
    }
}
