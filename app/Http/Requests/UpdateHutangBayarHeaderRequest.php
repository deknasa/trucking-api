<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'tglbukti' => 'required',
            'keterangan' => 'required',
            'bank' => 'required',
            'alatbayar' => 'required',
            'tglcair' => 'required'
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
            'keterangandetail.*' => 'keterangan detail',
            'bayar.*' => 'bayar',
        ];
    }
    
    public function messages()
    {
        return [
            'bayar.*.gt' => 'bayar wajib di isi & harus lebih besar dari 0',
        ];
    }
}
