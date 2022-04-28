<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusContainerRequest extends FormRequest
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
        return [
            'kodestatuscontainer' => 'required',
            'keterangan' => 'required',
            'statusaktif' => 'required|int',
        ];
    }

    public function attributes()
    {
        return [
            'kodestatuscontainer' => 'kode status container',
            'statusaktif' => 'status aktif',
        ];
    }
}
