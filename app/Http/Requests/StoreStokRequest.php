<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStokRequest extends FormRequest
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
            "namastok"=>'required',
            "kelompok"=>'required',
            "subkelompok"=>'required',
            "kategori"=>'required',
            "statusaktif"=>'required',
            "namaterpusat"=>'required',
            "keterangan"=>'required',
            // "qtymin"=>'required|gt:0|numeric',
            // "qtymax"=>'required|gt:0|numeric',
        ];
    }
}
