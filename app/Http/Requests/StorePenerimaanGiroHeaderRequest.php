<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StorePenerimaanGiroHeaderRequest extends FormRequest
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
                'required','date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'tgllunas' => 'required|date_format:d-m-Y'
        ];
        $relatedRequests = [
            StorePenerimaanGiroDetailRequest::class
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
        return [
            'tglbukti' => 'Tanggal Bukti',
            'diterimadari' => 'Diterima Dari',
            'tgllunas' => 'Tanggal Lunas',
            'tgljatuhtempo.*' => 'Tanggal jatuh tempo',
            'keterangan_detail.*' => 'Keterangan',
            'bank.*' => 'bank',
            'nowarkat.*' => 'nowarkat',
        ];
    }

    public function messages()
    {
        return [
            'nominal.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgllunas.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgljatuhtempo.*.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
