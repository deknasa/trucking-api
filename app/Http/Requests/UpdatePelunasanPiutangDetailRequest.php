<?php

namespace App\Http\Requests;

use App\Rules\BayarPotonganPelunasanPiutang;
use App\Rules\CekMaxBayarPelunasanPiutang;
use App\Rules\CekMaxBayarPelunasanPiutangEdit;
use App\Rules\CekMinusSisaPelunasanPiutang;
use App\Rules\CekMinusSisaPelunasanPiutangEdit;
use App\Rules\PotonganBayarPelunasanPiutang;
use App\Rules\RequiredCoaPotonganPelunasanPiutang;
use App\Rules\RequiredKetPotonganPelunasanPiutang;
use App\Rules\RequiredPotonganPelunasanPiutang;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePelunasanPiutangDetailRequest extends FormRequest
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
            'bayar.*' => ['required', 'numeric', 'min:0', new BayarPotonganPelunasanPiutang(), new CekMaxBayarPelunasanPiutangEdit()],
            'keterangan.*' => 'required',
            'sisa.*' => ['required', 'numeric', 'min:0', new CekMinusSisaPelunasanPiutangEdit()],
            'potongan.*' => ['numeric', 'min:0', new PotonganBayarPelunasanPiutang(),new RequiredPotonganPelunasanPiutang()],
            'nominallebihbayar.*' => ['numeric', 'min:0'],
            'keteranganpotongan.*' => new RequiredKetPotonganPelunasanPiutang(),
            'coapotongan.*' => new RequiredCoaPotonganPelunasanPiutang()
        ];
    }
}
