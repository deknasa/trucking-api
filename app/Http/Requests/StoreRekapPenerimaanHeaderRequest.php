<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StoreRekapPenerimaanHeaderRequest extends FormRequest
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
            'tgltransaksi' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
            "bank"=>"required",
        ];
        $relatedRequests = [
            StoreRekapPenerimaanDetailRequest::class
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
            "rekappenerimaan_id" =>"rekappenerimaan ",
            "keterangan_detail" =>"keterangan detail",
            "tgltransaksi_detail" =>"tgl transaksi detail",
            "penerimaan_nobukti" =>"penerimaan nobukti",
            "nominal" =>"nominal"
        ];
        $relatedRequests = [
            StoreRekapPenerimaanDetailRequest::class
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
        $messages= [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgltransaksi.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
        $relatedRequests = [
            StoreRekapPenerimaanDetailRequest::class
        ];
    
        foreach ($relatedRequests as $relatedRequest) {
            $messages = array_merge(
                $messages,
                (new $relatedRequest)->messages()
            );
        }
    
        return $messages;
    }
    
}
