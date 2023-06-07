<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class UpdatePengeluaranStokHeaderRequest extends FormRequest
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
            // "pengeluaranstok" => "required",
            // "pengeluaranstok_id" => "required",
            'trado' => 'required_without_all:gandengan,gudang',
            'gandengan' => 'required_without_all:trado,gudang',
            'gudang' => 'required_without_all:trado,gandengan',
        ];
    }
}
