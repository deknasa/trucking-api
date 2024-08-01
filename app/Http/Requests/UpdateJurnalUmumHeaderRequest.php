<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\JurnalUmumHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\validasiDestroyJurnalUmum;
use Illuminate\Validation\Rule;

class UpdateJurnalUmumHeaderRequest extends FormRequest
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
        $jurnalumum = new JurnalUmumHeader();
        $getData = $jurnalumum->find(request()->id);

        $rules = [
            'id' => [new validasiDestroyJurnalUmum()],
            'nobukti' => [Rule::in($getData->nobukti)],
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                'before_or_equal:'. date('d-m-Y'),
                new DateTutupBuku()
            ],
        ];

        $relatedRequests = [
            UpdateJurnalUmumDetailRequest::class
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
            'ketcoadebet_detail.*' => 'nama perkiraan (Debet)',
            'ketcoakredit_detail.*' => 'nama perkiraan (Kredit)',
            'nominal_detail.*' => 'Nominal',
            'keterangan_detail.*' => 'Keterangan',
        ];

        $relatedRequests = [
            UpdateJurnalUmumDetailRequest::class
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
        return [
            'nominal_detail.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan, 
        ];
    }
}
