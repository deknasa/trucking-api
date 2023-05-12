<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class UpdateInvoiceExtraHeaderRequest extends FormRequest
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
            "agen"=>"required",
            // "pelanggan"=>"required",
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                new DateTutupBuku()
            ],
        ];
        
        $relatedRequests = [
            UpdateInvoiceExtraDetailRequest::class
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
            'nominal_detail.*' => 'Harga',
            'keterangan_detail.*' => 'Keterangan',
        ];
        
        return $attributes;
    }

    public function messages() 
    {
        return [
            'nominal_detail.*.gt' => 'Harga Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
