<?php

namespace App\Http\Requests;

use App\Rules\CekMaxNominalPPGajiSupir;
use App\Rules\CekMaxNominalPSGajiSupir;
use App\Rules\CekMaxSisaPPGajiSupir;
use App\Rules\CekMaxSisaPSGajiSupir;
use App\Rules\ValidasiKeteranganBBMGajiSupir;
use App\Rules\ValidasiKeteranganDepositoGajiSupir;
use App\Rules\ValidasiTripGajiSupir;
use Illuminate\Foundation\Http\FormRequest;

class StoreGajiSupirDetailRequest extends FormRequest
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
        $rulesPinjSemua = [];
        if(request()->pinjSemua) {
            $rulesPinjSemua  = [
                'nominalPS.*' => ['required','numeric','gt:0', new CekMaxNominalPSGajiSupir()],
                'pinjSemua_sisa.*' => ['numeric','min:0']
            ];
        }

        $rulesPinjPribadi = [];
        
        if(request()->pinjPribadi) {
            $rulesPinjPribadi = [
                'nominalPP.*' => ['required','numeric','gt:0', new CekMaxNominalPPGajiSupir()],
                'pinjPribadi_sisa.*' => ['numeric','min:0']
            ];
        }

        $rulesDeposito = [];
        if(request()->nomDeposito > 0 ){
            $rulesDeposito = [
                'nomDeposito' => ['required','numeric','gt:0'],
                'ketDeposito' => new ValidasiKeteranganDepositoGajiSupir()
            ];
        }

        $rulesBBM = [];
        if(request()->nomBBM > 0) {
            $rulesBBM = [
                'nomBBM' => ['required','numeric','gt:0'],
                'ketBBM' => new ValidasiKeteranganBBMGajiSupir()
            ];
        }
        $rules = [
            'rincianId' => ['required']
        ];

        $rules = array_merge(
            $rules,
            $rulesPinjSemua,
            $rulesPinjPribadi,
            $rulesDeposito,
            $rulesBBM
        );

        return $rules;
    }
    
}
