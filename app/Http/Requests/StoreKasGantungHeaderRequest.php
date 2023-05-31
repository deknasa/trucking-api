<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Penerima;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StoreKasGantungHeaderRequest extends FormRequest
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
        $bank_id = $this->bank_id;
        $rulesBank_id = [];
        if ($bank_id != null) {
            if ($bank_id == 0) {
                $rulesBank_id = [
                    'bank_id' => ['required', 'numeric', 'min:1']
                ];
            }
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1']
            ];  
        }

        $penerima_id = $this->penerima_id;
        $rulesPenerima_id = [];
        if ($penerima_id != null) {
            if ($penerima_id == 0) {
                $rulesPenerima_id = [
                    'penerima_id' => ['required', 'numeric', 'min:1']
                ];
            } else {
                if ($this->penerima == '') {
                    $rulesPenerima_id = [
                        'penerima' => ['required']
                    ];
                }
            }
        } else if ($penerima_id == null && $this->penerima != '') {
            $rulesPenerima_id = [
                'penerima_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $rules = [
            'tglbukti' => [
                'required','date_format:d-m-Y',
                'date_equals:'.date('d-m-Y'),
                new DateTutupBuku()
            ],
            'bank' => 'required',
        ];
        $relatedRequests = [
            StoreKasGantungDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesBank_id,
                $rulesPenerima_id
            );
        }
        
        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tanggal Bukti',
            'nominal.*' => 'Nominal',
            'keterangan_detail.*' => 'Keterangan',
        ];
        
        return $attributes;
    }

    public function messages() 
    {
        return [            
            'bank_id.required' => ':attribute ' . app(ErrorController::class)->geterror('HPDL')->keterangan,
            'penerima_id.required' => ':attribute ' . app(ErrorController::class)->geterror('HPDL')->keterangan,
            'nominal.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
