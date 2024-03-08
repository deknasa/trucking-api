<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use App\Rules\DestroyKasGantung;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidasiDestroyKasGantungHeader ;

class DestroyKasGantungHeaderRequest extends FormRequest
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
            // 'nobukti' => new DestroyKasGantung(),
            'id' => [ new ValidasiDestroyKasGantungHeader()],
            'tglbukti' => new DateTutupBuku()
        ];
    }
}
