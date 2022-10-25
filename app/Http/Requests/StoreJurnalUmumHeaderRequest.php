<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJurnalUmumHeaderRequest extends FormRequest
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
            'tglbukti' => 'required',
            'keterangan' => 'required',
        ];

        $relatedRequests = [
            StoreJurnalUmumDetailRequest::class
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
            'coadebet_detail.*' => 'Coa Debet',
            'coakredit_detail.*' => 'Coa Kredit',
            'nominal_detail.*' => 'Nominal',
            'keterangan_detail.*' => 'Keterangan',
        ];

        $relatedRequests = [
            StoreJurnalUmumDetailRequest::class
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
            'nominal_detail.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
