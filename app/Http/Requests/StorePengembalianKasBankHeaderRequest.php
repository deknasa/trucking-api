<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateTutupBuku;
use Illuminate\Foundation\Http\FormRequest;

class StorePengembalianKasBankHeaderRequest extends FormRequest
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
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'alatbayar' => 'required',
            'dibayarke' => 'required',

            'bank' => 'required',
        ];
        $relatedRequests = [
            StorePengembalianKasBankDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }
        
        return $rules;
    }
    
    public function attributes() {
        return [
            'tglbukti' => 'tanggal bukti',
            'alatbayar' => 'alat bayar',
            'dibayarke' => 'dibayar ke',
            'ketcoadebet.*' => 'nama perkiraan (debet)',
            'keterangan_detail.*' => 'keterangan detail'
        ];
    }
    
    public function messages() 
    {
        return [
            'nominal_detail.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
