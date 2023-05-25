<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateTutupBuku;
use Illuminate\Foundation\Http\FormRequest;

class StorePelunasanPiutangHeaderRequest extends FormRequest
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
            'bank' => 'required',
            'agen' => 'required',
            'alatbayar' => 'required',
        ];

        $relatedRequests = [
            StorePelunasanPiutangDetailRequest::class
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
            'tglbukti' => 'Tanggal Bukti',
            'alatbayar' => 'alat bayar',
            'bayar.*' => 'Nominal Bayar',
            'keterangan.*' => 'keterangan'
        ];

        return $attributes;
    }

    public function messages()
    {
        return [
            'piutang_id.required' => 'PIUTANG '.app(ErrorController::class)->geterror('WP')->keterangan,
            'sisa.*.min' => 'SISA '.app(ErrorController::class)->geterror('NTM')->keterangan,
            'bayar.*.gt' => app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'bayar.*.numeric' => 'nominal harus '.app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
