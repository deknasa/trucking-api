<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateTutupBuku;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePengeluaranHeaderRequest extends FormRequest
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
        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'alatbayar' => 'required',
            'bank' => 'required',
        ];
        $relatedRequests = [
            StorePengeluaranDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }

        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            'dibayarke' => 'Dibayar Ke',
            'transferkeac' => 'Transfer Ke Account',
            'transferkean' => 'Transfer Ke An.',
            'transferkebank' => 'Transfer Ke Bank',
            'alatbayar' => 'Alat Bayar',
            'nowarkat.*' => 'No Warkat',
            'tgljatuhtempo.*' => 'Tanggal Jatuh Tempo',
            'nominal_detail.*' => 'Nominal',
            'keterangan_detail.*' => 'Keterangan',
            'ketcoadebet.*' => 'nama perkiraan'
        ];
        $relatedRequests = [
            UpdatePengeluaranDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $attributes = array_merge(
                $attributes,
                (new $relatedRequest)->attributes()
            );
        }
        return $attributes;
    }

    public function messages()
    {
        return [
            'nominal_detail.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgljatuhtempo.*.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
