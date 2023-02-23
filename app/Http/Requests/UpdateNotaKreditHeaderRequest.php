<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class UpdateNotaKreditHeaderRequest extends FormRequest
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
            "tglbukti" => [
                "required",
                new DateTutupBuku()
            ],
            // "tglapproval"=>"required",
            "tgllunas"=>"required",
            "pelunasanpiutang_nobukti"=>"required",
            "keterangan"=>"required",
            // "statusformat"=>"required",
            // "statusapproval"=>"required",
        ];
    }
}
