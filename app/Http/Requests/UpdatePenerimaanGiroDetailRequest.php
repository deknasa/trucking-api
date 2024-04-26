<?php

namespace App\Http\Requests;

use App\Rules\validasiBankPenerimaanGiro;
use App\Rules\validasiNoWarkatPenerimaanGiro;
use App\Rules\ValidateTglJatuhTempoPenerimaanGiro;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePenerimaanGiroDetailRequest extends FormRequest
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
            'tgljatuhtempo' => 'required|array',
            'tgljatuhtempo.*' => ['required','date_format:d-m-Y', new ValidateTglJatuhTempoPenerimaanGiro()],
            'nominal' => 'required|array',
            'nominal.*' => 'required|numeric|gt:0',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required',
            'bank' => 'required|array',
            'bank.*' => ['required', new validasiBankPenerimaanGiro()],
            'nowarkat' => 'required|array',
            'nowarkat.*' => ['required', new validasiNoWarkatPenerimaanGiro()],
        ];
    }
}
