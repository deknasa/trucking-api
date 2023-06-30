<?php

namespace App\Http\Requests;

use App\Rules\CekMaxBayarPelunasanPiutang;
use App\Rules\CekMinusSisaPelunasanPiutang;
use App\Rules\RequiredCoaPotonganPelunasanPiutang;
use App\Rules\RequiredKetPotonganPelunasanPiutang;
use App\Rules\RequiredPotonganPelunasanPiutang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StorePelunasanPiutangDetailRequest extends FormRequest
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
            'bayar.*' => ['required', 'numeric', 'gt:0', new CekMaxBayarPelunasanPiutang()],
            'keterangan.*' => 'required',
            'sisa.*' => ['required', 'numeric', 'min:0', new CekMinusSisaPelunasanPiutang()],
            'potongan.*' => ['numeric', 'min:0', new RequiredPotonganPelunasanPiutang()],
            'nominallebihbayar.*' => ['numeric', 'min:0'],
            'keteranganpotongan.*' => new RequiredKetPotonganPelunasanPiutang(),
            'coapotongan.*' => new RequiredCoaPotonganPelunasanPiutang()
        ];
    }
}
