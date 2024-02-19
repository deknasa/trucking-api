<?php

namespace App\Http\Requests;

use App\Rules\AkunPusatPenerimaanDetail;
use App\Rules\BankPelangganIdPenerimaanDetail;
use App\Rules\BankPelangganPenerimaanDetail;
use App\Rules\CoaKreditPenerimaanDetail;
use App\Rules\ExistAkunPusat;
use App\Rules\ExistBankPelangganPenerimaanDetail;
use App\Rules\validasiNominalDetail;
use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanDetailRequest extends FormRequest
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
        $datequals = '';
        if(request()->penerimaangiro_nobukti == ''){
            $datequals = 'date_equals:'.request()->tglbukti;
        }
        $rules = [
            'ketcoakredit.*' => 'required',
            'coakredit.*' =>  [new CoaKreditPenerimaanDetail, new AkunPusatPenerimaanDetail()],
            'tgljatuhtempo.*' => ['required','date_format:d-m-Y', $datequals],
            'nominal_detail.*' => ['required', 'numeric', new validasiNominalDetail()],
            'keterangan_detail.*' => 'required',
            'bankpelanggan.*' => [new BankPelangganPenerimaanDetail()],
            'bankpelanggan_id.*' => [new BankPelangganIdPenerimaanDetail(), new ExistBankPelangganPenerimaanDetail()]
        ];

        return $rules;
    }

    public function attributes() {
        return [
            'ketcoakredit.*' => 'nama perkiraan (kredit)',
            'coakredit.*' => 'perkiraan (kredit)',
            'tgljatuhtempo.*' => 'tgl jatuh tempo',
            'nominal_detail.*' => 'nominal',
            'keterangan_detail.*' => 'keterangan',
            'bankpelanggan.*' => 'bank pelanggan',
            'bankpelanggan_id.*' => 'bank pelanggan',
        ];
    }

}
