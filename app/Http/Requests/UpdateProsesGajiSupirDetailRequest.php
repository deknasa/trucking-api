<?php

namespace App\Http\Requests;

use App\Models\ProsesGajiSupirHeader;
use App\Rules\ExistBank;
use App\Rules\GetBbmEBS;
use App\Rules\GetBoronganEBS;
use App\Rules\GetDepositoEBS;
use App\Rules\GetPinjPribadiEBS;
use App\Rules\GetPinjSemuaEBS;
use App\Rules\GetUangjalanEBS;
use App\Rules\RuleBankBbmEBSEdit;
use App\Rules\RuleBankDepositoEBSEdit;
use App\Rules\RuleBankEBSEdit;
use App\Rules\RuleBankPotPribadiEBSEdit;
use App\Rules\RuleBankPotSemuaEBSEdit;
use App\Rules\RuleBankUangjalanEBSEdit;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProsesGajiSupirDetailRequest extends FormRequest
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
        $prosesGaji = new ProsesGajiSupirHeader();
        $getDataProsesGaji = $prosesGaji->findAll(request()->id);

        $rulesPostingRincian = [];
        $bank_idPR = request()->bank_idPR;
        if ($bank_idPR != null) {
            $rulesPostingRincian = [
                'bank_idPR' => ['required', 'numeric', 'min:1',new RuleBankEBSEdit(), new ExistBank()]
            ];
        } else if ($bank_idPR == null && $this->bankPR != '') {
            $rulesPostingRincian = [
                'bank_idPR' => ['required', 'numeric', 'min:1',new RuleBankEBSEdit(), new ExistBank()]
            ];
        }

        $rulesPostingRincianBankIdPP = [];
        if (request()->nomPP > 0) {
            $bank_idPP = request()->bank_idPP;
            if ($bank_idPP != null) {
                $rulesPostingRincianBankIdPP = [
                    'bankPP' => 'required',
                    'bank_idPP' => ['required', 'numeric', 'min:1', new RuleBankPotPribadiEBSEdit(), new ExistBank()]
                ];
            } else if ($bank_idPP == null && $this->bankPP != '') {
                $rulesPostingRincianBankIdPP = [
                    'bank_idPP' => ['required', 'numeric', 'min:1', new RuleBankPotPribadiEBSEdit(), new ExistBank()]
                ];
            }
        }
        $rulesPostingRincianBankIdPS = [];
        if (request()->nomPS > 0) {
            $bank_idPS = request()->bank_idPS;
            if ($bank_idPS != null) {
                $rulesPostingRincianBankIdPS = [
                    'bankPS' => 'required',
                    'bank_idPS' => ['required', 'numeric', 'min:1',new RuleBankPotSemuaEBSEdit, new ExistBank()]
                ];
            } else if ($bank_idPS == null && $this->bankPS != '') {
                $rulesPostingRincianBankIdPS = [
                    'bank_idPS' => ['required', 'numeric', 'min:1',new RuleBankPotSemuaEBSEdit, new ExistBank()]
                ];
            }
        }
        $rulesPostingRincianBankIdDeposito = [];
        if (request()->nomDeposito > 0) {
            $bank_idDeposito = request()->bank_idDeposito;
            if ($bank_idDeposito != null) {
                $rulesPostingRincianBankIdDeposito = [
                    'bankDeposito' => 'required',
                    'bank_idDeposito' => ['required', 'numeric', 'min:1', new RuleBankDepositoEBSEdit(), new ExistBank()]
                ];
            } else if ($bank_idDeposito == null && $this->bankDeposito != '') {
                $rulesPostingRincianBankIdDeposito = [
                    'bank_idDeposito' => ['required', 'numeric', 'min:1', new RuleBankDepositoEBSEdit(), new ExistBank()]
                ];
            }
        }
        $rulesPostingRincianBankIdBBM = [];
        if (request()->nomBBM > 0) {
            $bank_idBBM = request()->bank_idBBM;
            if ($bank_idBBM != null) {
                $rulesPostingRincianBankIdBBM = [
                    'bankBBM' => 'required',
                    'bank_idBBM' => ['required', 'numeric', 'min:1', new RuleBankBbmEBSEdit(), new ExistBank()]
                ];
            } else if ($bank_idBBM == null && $this->bankBBM != '') {
                $rulesPostingRincianBankIdBBM = [
                    'bank_idBBM' => ['required', 'numeric', 'min:1', new RuleBankBbmEBSEdit(), new ExistBank()]
                ];
            }
        }
        $rulesPostingRincianBankIdUangjalan = [];
        if (request()->nomUangjalan > 0) {
            $bank_idUangjalan = request()->bank_idUangjalan;
            if ($bank_idUangjalan != null) {
                $rulesPostingRincianBankIdUangjalan = [
                    'bankUangjalan' => 'required',
                    'bank_idUangjalan' => ['required', 'numeric', 'min:1', new RuleBankUangjalanEBSEdit(), new ExistBank()]
                ];
            } else if ($bank_idUangjalan == null && $this->bankUangjalan != '') {
                $rulesPostingRincianBankIdUangjalan = [
                    'bank_idUangjalan' => ['required', 'numeric', 'min:1', new RuleBankUangjalanEBSEdit(), new ExistBank()]
                ];
            }
        }


        $rules = [
            'rincianId' => 'required',
            'nomPR' => ['required', 'numeric', 'gt:0', new GetBoronganEBS()],
            'bankPR' => 'required',
            'nomPS' => ['required', 'numeric', 'min:0', new GetPinjSemuaEBS()],
            'nomPP' => ['required', 'numeric', 'min:0', new GetPinjPribadiEBS()],
            'nomDeposito' => ['required', 'numeric', 'min:0', new GetDepositoEBS()],
            'nomBBM' => ['required', 'numeric', 'min:0', new GetBbmEBS()],
            'nomUangjalan' => ['required', 'numeric', 'min:0', new GetUangjalanEBS()],

        ];

        $rules = array_merge(
            $rules,
            $rulesPostingRincian,
            $rulesPostingRincianBankIdPP,
            $rulesPostingRincianBankIdPS,
            $rulesPostingRincianBankIdDeposito,
            $rulesPostingRincianBankIdBBM,
            $rulesPostingRincianBankIdUangjalan
        );
        return $rules;
    }
}
