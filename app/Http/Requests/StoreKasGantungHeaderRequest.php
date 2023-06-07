<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Penerima;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\ExistPenerima;

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
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }

        // $penerima_id = $this->penerima_id;
        // $rulesPenerima_id = [];
        // if ($penerima_id != null) {

        //     $rulesPenerima_id = [
        //         'penerima' => ['required'],
        //         'penerima_id' => ['required', 'numeric', 'min:1', new ExistPenerima()]
        //     ];
        // } else if ($penerima_id == null && $this->penerima != '') {
        //     $rulesPenerima_id = [
        //         'penerima_id' => ['required', 'numeric', 'min:1', new ExistPenerima()]
        //     ];
        // }

        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y')
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
                // $rulesPenerima_id
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
