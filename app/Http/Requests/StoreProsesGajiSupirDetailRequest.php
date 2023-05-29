<?php

namespace App\Http\Requests;

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

        $rulesPostingRincian = [];
        if (request()->nomPR > 0 || request()->bank_idPR != '') {
            $bank_idPR = request()->bank_idPR;
            $rulesPostingRincianBankId = [];
            if ($bank_idPR != null) {
                if ($bank_idPR == 0) {
                    $rulesPostingRincianBankId = [
                        'bank_idPR' => ['required','numeric','min:1']
                    ];
                }
            } else if ($bank_idPR == null && $this->bankPR != '') {
                $rulesPostingRincianBankId = [
                    'bank_idPR' => ['required','numeric','min:1']
                ];
            }
            $rulesPostingRincian = [
                'nomPR' => ['required','numeric','gt:0'],
                'bankPR' => 'required'
            ];
            $rulesPostingRincian = array_merge($rulesPostingRincian, $rulesPostingRincianBankId);
        }
        //HARUS DI CEK PAKAI QUERY BERDASARKAN TGL DARI&SAMPAI
        if (request()->nomPP > 0 || request()->bank_idPP != '') {
            $bank_idPP = request()->bank_idPP;
            $rulesPostingRincianBankIdPP = [];
            if ($bank_idPP != null) {
                if ($bank_idPP == 0) {
                    $rulesPostingRincianBankIdPP = [
                        'bank_idPP' => ['required','numeric','min:1']
                    ];
                }
            } else if ($bank_idPP == null && $this->bankPP != '') {
                $rulesPostingRincianBankIdPP = [
                    'bank_idPP' => ['required','numeric','min:1']
                ];
            }
            $rulesPostingRincian = [
                'nomPP' => ['required','numeric',':0'],
                'bankPP' => 'required'
            ];
            $rulesPostingRincian = array_merge($rulesPostingRincian, $rulesPostingRincianBankIdPP);
        }
        $rules = [
            'rincianId' => 'required'
        ];

        $rules = array_merge(
            $rules,
            $rulesPostingRincian
        );
        return $rules;
    }
}
