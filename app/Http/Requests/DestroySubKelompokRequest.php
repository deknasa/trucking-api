<?php

namespace App\Http\Requests;

use App\Rules\ValidasiDestroySubKelompok;
use Illuminate\Foundation\Http\FormRequest;

class DestroySubKelompokRequest extends FormRequest
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
        return [
            'id' => new ValidasiDestroySubKelompok()
        ];
    }
}
