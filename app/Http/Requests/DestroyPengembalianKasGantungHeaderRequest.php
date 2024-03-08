<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use App\Rules\DestroyPengembalianKasGantung;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidasiDestroyPengembaliankasGantungHeader ;
use App\Http\Controllers\Api\PengembaliankasGantungHeaderController;

class DestroyPengembalianKasGantungHeaderRequest extends FormRequest
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
            // 'nobukti' => new DestroyPengembalianKasGantung(),
            'id' => [ new ValidasiDestroyPengembalianKasGantungHeader()],            
            'tglbukti' => [
                'required','date_format:d-m-Y',
                new DateTutupBuku()
            ],
        ];
    }
}
