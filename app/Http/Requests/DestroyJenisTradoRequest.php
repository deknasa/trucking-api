<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\JenisTrado;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyJenisTrado;

class DestroyJenisTradoRequest extends FormRequest
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

        $jenistrado = new JenisTrado();
        $cekdata = $jenistrado->cekValidasihapus($this->id);

        return [
            'id' => [new ValidasiDestroyJenisTrado($cekdata['kondisi'])],
        ];
    }
}
