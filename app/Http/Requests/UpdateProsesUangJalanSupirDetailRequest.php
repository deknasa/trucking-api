<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use App\Models\ProsesUangJalanSupirDetail;
use App\Rules\CekAllTotalProsesUangJalan;
use App\Rules\CekBankAdjustProsesUangJalanEdit;
use App\Rules\CekBankDepositProsesUangJalanEdit;
use App\Rules\CekBankPengembalianProsesUangJalanEdit;
use App\Rules\CekBankTransferProsesUangJalanEdit;
use App\Rules\CekDepositProsesUangJalanEdit;
use App\Rules\CekMinusSisaPinjamanProsesUangJalan;
use App\Rules\CekNomAdjustProsesUangJalan;
use App\Rules\CekNomPinjamanProsesUangJalan;
use App\Rules\CekTransferProsesUangJalanEdit;
use App\Rules\ExistBank;
use App\Rules\ExistBankProsesUangJalan;
use App\Rules\ValidasiKeteranganProsesUangJalan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProsesUangJalanSupirDetailRequest extends FormRequest
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
        $rulesDeposito = [];
        if (request()->nilaideposit > 0 || request()->bankdeposit != '') {
            $rulesDeposito = [
                'nilaideposit' => ['required', 'numeric', 'min:0', new CekDepositProsesUangJalanEdit()],
                'keterangandeposit' =>  ['required', new ValidasiKeteranganProsesUangJalan()],
                'bankdeposit' => 'required'
            ];
        }

        $bank_iddeposit = $this->bank_iddeposit;
        $rulesBankIdDeposit = [];
        if ($bank_iddeposit == null && $this->bankdeposit != '') {
            $rulesBankIdDeposit = [
                'bank_iddeposit' => ['required', 'numeric', 'min:1',new CekBankDepositProsesUangJalanEdit(), new ExistBankProsesUangJalan()]
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
                'bank_idpengembalian' => ['required', 'numeric', 'min:1',new CekBankPengembalianProsesUangJalanEdit(), new ExistBankProsesUangJalan()]
            ];
        }

        $totalValidasi = [
            'totalAll' => new CekAllTotalProsesUangJalan()
        ];
        $rules = [
            'keterangantransfer.*' => 'required',
            'nilaitransfer.*' => ['required', 'gt:0', 'numeric', new CekTransferProsesUangJalanEdit()],
            'banktransfer.*' => 'required',
            'bank_idtransfer.*' => ['required', 'numeric', 'min:1', new CekBankTransferProsesUangJalanEdit(), new ExistBankProsesUangJalan()],
            'nilaiadjust' => ['required', 'gt:0', 'numeric'],
            'keteranganadjust' => 'required',
            'bankadjust' => 'required',
            'bank_idadjust' => ['required', 'numeric', 'min:1', new CekBankAdjustProsesUangJalanEdit(), new ExistBankProsesUangJalan()]
        ];

        $rules = array_merge(
            $rules,
            $rulesDeposito,
            $rulesBankIdDeposit,
            $rulePengembalian,
            $rulesBankIdPengembalian,
            $totalValidasi
        );
        return $rules;
    }
}
