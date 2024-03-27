<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use App\Rules\validasiDestroyProsesUangJalanSupir;
use Illuminate\Foundation\Http\FormRequest;

class DestroyProsesUangJalanSupirHeaderRequest extends FormRequest
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
            'id' => new validasiDestroyProsesUangJalanSupir(),
            'tglbukti' => [
                'required','date_format:d-m-Y',
                new DateTutupBuku()
            ],
        ];
    }
}
