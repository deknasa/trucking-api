<?php

namespace App\Http\Requests;

use App\Models\ProsesUangJalanSupirHeader;
use App\Rules\CekAllTotalProsesUangJalan;
use App\Rules\CekBankTransferProsesUangJalan;
use App\Rules\CekMinusSisaPinjamanProsesUangJalan;
use App\Rules\CekNomAdjustProsesUangJalan;
use App\Rules\CekNomPinjamanProsesUangJalan;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\ExistBankProsesUangJalan;
use App\Rules\ExistBankTransferProsesUangJalan;
use App\Rules\ValidasiKeteranganProsesUangJalan;
use Illuminate\Foundation\Http\FormRequest;

class StoreProsesUangJalanSupirDetailRequest extends FormRequest
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
        $bankadjust_id = $this->bankadjust_id;
        $rulesBankAdjust_id = [];
        if ($bankadjust_id != null) {
            $rulesBankAdjust_id = [
                'bankadjust_id' => ['required', 'numeric', 'min:1', new ExistBankProsesUangJalan()]
            ];
        } else if ($bankadjust_id == null && $this->bankadjust != '') {
            $rulesBankAdjust_id = [
                'bankadjust_id' => ['required', 'numeric', 'min:1', new ExistBankProsesUangJalan()]
            ];
        }
        $rulesDeposito = [];
        if (request()->nilaideposit > 0 && request()->bankdeposit != '') {
            $rulesDeposito = [

                'tgldeposit' =>  [
                    "required", 'date_format:d-m-Y',
                    new DateTutupBuku(),
                ],
                'nilaideposit' => ['required', 'numeric', 'min:0'],
                'keterangandeposit' => ['required', new ValidasiKeteranganProsesUangJalan()],
                'bankdeposit' => 'required'
            ];
        }

        $bank_iddeposit = $this->bank_iddeposit;
        $rulesBankIdDeposit = [];
        if ($bank_iddeposit == null && $this->bankdeposit != '') {
            $rulesBankIdDeposit = [
                'bank_iddeposit' => ['required', 'numeric', 'min:1', new ExistBankProsesUangJalan()]
            ];
        }

        $rulePengembalian = [];
        if (request()->pjt_id) {
            $rulePengembalian = [
                'nombayar.*' => ['required', 'numeric', 'gt:0', new CekNomPinjamanProsesUangJalan()],
                'keteranganpinjaman.*' => 'required',
                'sisa.*' => ['required', 'numeric', 'min:0', new CekMinusSisaPinjamanProsesUangJalan()],
                'bankpengembalian' => 'required'
            ];
        }
        $bank_idpengembalian = $this->bank_idpengembalian;
        $rulesBankIdPengembalian = [];
        if ($bank_idpengembalian == null && $this->bankpengembalian != '') {
            $rulesBankIdPengembalian = [
                'bank_idpengembalian' => ['required', 'numeric', 'min:1', new ExistBankProsesUangJalan()]
            ];
        }

        $totalValidasi = [
            'totalAll' => new CekAllTotalProsesUangJalan()
        ];
        $rules = [

            'tgltransfer.*' =>  [
                "required", 'date_format:d-m-Y',
                new DateTutupBuku(),
            ],
            'keterangantransfer.*' => 'required',
            'nilaitransfer.*' => ['required', 'gt:0', 'numeric'],
            'banktransfer.*' => 'required',
            'bank_idtransfer.*' => [new CekBankTransferProsesUangJalan(), new ExistBankTransferProsesUangJalan()],
            'tgladjust' =>  [
                "required", 'date_format:d-m-Y',
                new DateTutupBuku(),
            ],
            'nilaiadjust' => ['required', 'gt:0', 'numeric'],
            'keteranganadjust' => 'required',
            'bankadjust' => 'required',
        ];

        $rules = array_merge(
            $rules,
            $rulesBankAdjust_id,
            $rulesDeposito,
            $rulesBankIdDeposit,
            $rulePengembalian,
            $rulesBankIdPengembalian,
            $totalValidasi
        );

        return $rules;
    }

    public function attributes()
    {
        return [
            'tgltransfer.*' => 'tgl transfer',
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
