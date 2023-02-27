<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StorePenerimaanStokHeaderRequest extends FormRequest
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
            'tglbukti' => [
                'required',
                new DateTutupBuku()
            ],
            // "keterangan"=> "required", 
            "penerimaanstok" => "required",
            "penerimaanstok_id" => "required",
            "modifiedby"=> "string", 
        ];
    }
}
