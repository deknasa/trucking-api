<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StoreServiceInHeaderRequest extends FormRequest
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
            'trado' => 'required',
            "tglmasuk" => [
                "required",
                new DateTutupBuku()
            ],
        ];
        $relatedRequests = [
            StoreServiceInDetailRequest::class
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
            'tglmasuk' => 'tanggal masuk',
            'karyawan.*' => 'mekanik',
            'keterangan_detail.*' => 'keterangan detail'
        ];
    }
    
    public function messages() 
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglmasuk.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
