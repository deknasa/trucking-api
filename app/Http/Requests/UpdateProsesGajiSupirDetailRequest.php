<?php

namespace App\Http\Requests;

use App\Models\ProsesGajiSupirHeader;
use App\Rules\ExistBank;
use App\Rules\ExistBankProsesUangJalan;
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

        $rules = [
            'nomPR' => ['required', 'numeric', 'gt:0', new GetBoronganEBS()],
            'nomPS' => ['required', 'numeric', 'min:0', new GetPinjSemuaEBS()],
            'nomPP' => ['required', 'numeric', 'min:0', new GetPinjPribadiEBS()],
            'nomDeposito' => ['required', 'numeric', 'min:0', new GetDepositoEBS()],
            'nomBBM' => ['required', 'numeric', 'min:0', new GetBbmEBS()],
            'nomUangjalan' => ['required', 'numeric', 'min:0', new GetUangjalanEBS()],

        ];

        return $rules;
    }
}
