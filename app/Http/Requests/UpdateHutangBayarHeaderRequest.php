<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class UpdateHutangBayarHeaderRequest extends FormRequest
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
            'bank' => 'required',
            'alatbayar' => 'required',
            'tglcair' => 'required|date_format:d-m-Y',
            'supplier' => 'required'
        ];
        $relatedRequests = [
            UpdateHutangBayarDetailRequest::class
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
            'hutang_id' => 'Pilih Hutang',
            'keterangan.*' => 'keterangan detail',
            'bayar.*' => 'bayar',
        ];
    }
    
    public function messages()
    {
        return [
            'hutang_id.required' => 'HUTANG '.app(ErrorController::class)->geterror('WP')->keterangan,
            'sisa.*.min' => 'SISA '.app(ErrorController::class)->geterror('NTM')->keterangan,
            'bayar.*.numeric' => 'nominal harus '.app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
            'bayar.*.gt' =>  app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglcair.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
