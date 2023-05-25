<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class UpdatePengembalianKasGantungHeaderRequest extends FormRequest
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
           
            "bank" => "required",
            "tgldari" => "required",
            "tglsampai" => "required",
        ];
        $relatedRequests = [
            UpdatePengembalianKasGantungDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }
        
        return $rules;
    }
    public function messages() 
    {
        return [
                'kasgantungdetail_id.required' => 'KASGANTUNG '.app(ErrorController::class)->geterror('WP')->keterangan,
                'sisa.*.min' => 'SISA '.app(ErrorController::class)->geterror('NTM')->keterangan,
                'nominal.*.numeric' => 'nominal harus '.app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
                'nominal.*.gt' => ':attribute ' .  app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
                'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
                'tgldari.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
                'tglsampai.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
