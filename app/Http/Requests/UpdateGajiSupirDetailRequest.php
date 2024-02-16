<?php

namespace App\Http\Requests;

use App\Rules\ValidasiTripGajiSupir;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGajiSupirDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
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
                'nominalPS.*' => ['required','numeric','gt:0'],
                'pinjSemua_sisa.*' => ['numeric','min:0']
            ];
        }

        $rulesPinjPribadi = [];
        
        if(request()->pinjPribadi) {
            $rulesPinjPribadi = [
                'nominalPP.*' => ['required','numeric','gt:0'],
                'pinjPribadi_sisa.*' => ['numeric','min:0']
            ];
        }

        $rulesDeposito = [];
        if(request()->nomDeposito > 0){
            $rulesDeposito = [
                'nomDeposito' => ['required','numeric','gt:0'],
            ];
        }

        $rulesBBM = [];
        if(request()->nomBBM > 0) {
            $rulesBBM = [
                'nomBBM' => ['required','numeric','gt:0'],
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
