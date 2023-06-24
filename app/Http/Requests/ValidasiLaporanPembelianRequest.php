<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class ValidasiLaporanPembelianRequest extends FormRequest
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
            'dari' => ['required', 'date_format:d-m-Y'],
            'sampai' => ['required', 'date_format:d-m-Y'],
            'supplierdari' => ['required'],
            'suppliersampai' => ['required'],
            'status' => ['required'],
        ];
    }

    public function attributes()
    {
        return [
            'limit' => 'sampai',
        ];
    }

    public function messages()
    {
        return [
            'dari.required' => 'Kolom tanggal dari harus diisi.',
            'dari.date_format' => 'Format tanggal dari harus berupa dd-mm-yyyy.',
            'sampai.required' => 'Kolom tanggal sampai harus diisi.',
            'sampai.date_format' => 'Format tanggal sampai harus berupa dd-mm-yyyy.',
            'supplierdari.required' => 'Kolom supplier dari harus diisi.',
            'suppliersampai.required' => 'Kolom supplier sampai harus diisi.',
            'status.required' => 'Kolom status harus diisi.',
        ];
    }
}
