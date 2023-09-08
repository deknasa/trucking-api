<?php

namespace App\Http\Requests;

use App\Models\ProsesUangJalanSupirHeader;
use App\Rules\CekAllTotalProsesUangJalan;
use App\Rules\CekBankTransferProsesUangJalan;
use App\Rules\CekMinusSisaPinjamanProsesUangJalan;
use App\Rules\CekNomAdjustProsesUangJalan;
use App\Rules\CekNomPinjamanProsesUangJalan;
use App\Rules\ExistBank;
use App\Rules\ExistBankProsesUangJalan;
use App\Rules\ExistBankTransferProsesUangJalan;
use Illuminate\Foundation\Http\FormRequest;

class StoreProsesUangJalanSupirDetailTransferRequest extends FormRequest
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
        
        $rules = [
            'keterangantransfer.*' => 'required',
            'nilaitransfer.*' => ['required', 'gt:0', 'numeric'],
            'banktransfer.*' => 'required',
            'bank_idtransfer.*' => [new CekBankTransferProsesUangJalan(), new ExistBankTransferProsesUangJalan()],
        ];


        return $rules;
    }

    public function attributes() {
        return [
            'bankadjust_id' => 'bank adjust',
            'nilaideposit' => 'nilai deposit',
            'keterangandeposit' => 'keterangan deposit',
            'bankdeposit' => 'bank deposit',
            'bank_iddeposit' => 'bank deposit',
            'nombayar.*' => 'nominal bayar',
            'keteranganpinjaman.*' => 'keterangan pinjaman',
            'sisa.*' => 'sisa',
            'bankpengembalian' => 'bank pengembalian',
            'bank_idpengembalian' => 'bank pengembalian',
            'totalAll' => 'total All',
            'keterangantransfer.*' => 'keterangan transfer',
            'nilaitransfer.*' => 'nilai transfer',
            'banktransfer.*' => 'bank transfer',
            'bank_idtransfer.*' => 'bank transfer',
            'nilaiadjust' => 'nilai adjust',
            'keteranganadjust' => 'keterangan adjust',
            'bankadjust' => 'bank adjust',
        ];
    }

    

}
