<?php

namespace App\Http\Requests;

use App\Rules\CekMaxBayarPengembalianKasGantungEdit;
use App\Rules\CekMinusPengembalianKasGantungEdit;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePengembalianKasGantungDetailRequest extends FormRequest
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
            'kasgantungdetail_id' => 'required',
            'nominal.*' => ['required','numeric','gt:0', new CekMaxBayarPengembalianKasGantungEdit()],
            'keterangandetail.*' => 'required',
            'coadetail.*' => 'required',
            'sisa.*' => ['required','numeric','gt:0', new CekMinusPengembalianKasGantungEdit()],
        ];
    }
}
