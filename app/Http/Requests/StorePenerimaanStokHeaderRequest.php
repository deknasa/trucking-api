<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;

class StorePenerimaanStokHeaderRequest extends FormRequest
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
                'required',
                new DateTutupBuku()
            ],
            // "keterangan"=> "required", 
            "penerimaanstok" => "required",
            "penerimaanstok_id" => "required",
            "modifiedby"=> "string", 
        ];

        $relatedRequests = [
            StorePenerimaanStokDetailRequest::class
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
            'tglbukti' => 'Tgl Bukti',
            // 'penerimaanstok' => 'nama Penerimaan',
            'penerimaanstok' => 'Kode Penerimaan',
        ];

        $relatedRequests = [
            StorePenerimaanStokDetailRequest::class
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
        $messages = [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'penerimaanstok.required' => app(ErrorController::class)->geterror('WI')->keterangan,
        ];

        $relatedRequests = [
            StorePenerimaanTruckingDetailRequest::class
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
