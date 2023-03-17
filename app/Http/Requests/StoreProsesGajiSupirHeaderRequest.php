<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StoreProsesGajiSupirHeaderRequest extends FormRequest
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
            //
            'periode' => 'required',
            // 'tgldari' => 'required',
            // 'tglsampai' => 'required',
            "tglbukti" => [
                "required",
                new DateTutupBuku()
            ],
        ];
    }

    public function attributes() {
        return [
            'periode' => 'Periode',
            // 'tgldari' => 'Tanggal Dari',
            // 'tglsampai' => 'Tanggal Sampai',
            'tglbukti' => 'Tanggal Bukti'
        ];
    }
}
