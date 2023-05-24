<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\PreventInputType;

class StorePengembalianKasGantungHeaderRequest extends FormRequest
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
            "bank" => "required",
            "tgldari" => [
                'required', 'date_format:d-m-Y'
            ],
            "tglsampai" => [
                'required', 'date_format:d-m-Y'
            ],
        ];
        $relatedRequests = [
            StorePengembalianKasGantungDetailRequest::class
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
            'keterangandetail.*' => 'keterangan',
            'coadetail.*' => 'kode perkiraan',
        ];
    }
    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
