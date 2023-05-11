<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StoreHutangHeaderRequest extends FormRequest
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
            'supplier' => 'required'
        ];
        $relatedRequests = [
            StoreHutangDetailRequest::class
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
            'tgljatuhtempo.*' => 'Tanggal Jatuh Tempo',
            'total_detail.*' => 'Total',
            'keterangan_detail.*' => 'Keterangan'
        ];

        return $attributes;
    }
    public function messages() 
    {
        return [
            'total_detail.*.gt' => 'Total Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
