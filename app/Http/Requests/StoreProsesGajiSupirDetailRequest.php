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
use Illuminate\Foundation\Http\FormRequest;

class StoreProsesGajiSupirDetailRequest extends FormRequest
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
        //HARUS DI CEK PAKAI QUERY BERDASARKAN TGL DARI&SAMPAI
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
