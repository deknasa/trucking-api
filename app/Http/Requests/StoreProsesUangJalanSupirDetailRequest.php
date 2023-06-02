<?php

namespace App\Http\Requests;

use App\Models\ProsesUangJalanSupirHeader;
use App\Rules\CekAllTotalProsesUangJalan;
use App\Rules\CekMinusSisaPinjamanProsesUangJalan;
use App\Rules\CekNomAdjustProsesUangJalan;
use App\Rules\CekNomPinjamanProsesUangJalan;
use App\Rules\ExistBank;
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
                'bankadjust_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bankadjust_id == null && $this->bankadjust != '') {
            $rulesBankAdjust_id = [
                'bankadjust_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }
        $rulesDeposito = [];
        if (request()->nilaideposit > 0 || request()->keterangandeposit != '' || request()->bankdeposit != '') {
            $rulesDeposito = [
                'nilaideposit' => ['required', 'numeric', 'min:0'],
                'keterangandeposit' => 'required',
                'bankdeposit' => 'required'
            ];
        }

        $bank_iddeposit = $this->bank_iddeposit;
        $rulesBankIdDeposit = [];
        if ($bank_iddeposit == null && $this->bankdeposit != '') {
            $rulesBankIdDeposit = [
                'bank_iddeposit' => ['required', 'numeric', 'min:1', new ExistBank()]
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
                'bank_idpengembalian' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }

        $totalValidasi = [
            'totalAll' => new CekAllTotalProsesUangJalan()
        ];

        $rules = [
            'keterangantransfer.*' => 'required',
            'nilaitransfer.*' => ['required', 'gt:0', 'numeric'],
            'banktransfer.*' => 'required',
            'banktransfer_id.*' => ['required', 'numeric', 'min:1', new ExistBank()],
            'nilaiadjust' => ['required', 'gt:0', 'numeric', new CekNomAdjustProsesUangJalan()],
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
}
