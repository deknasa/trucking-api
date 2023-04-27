<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePelunasanPiutangHeaderRequest extends FormRequest
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
            'bank' => 'required',
            'agen' => 'required',
            'alatbayar' => 'required',
        ];

        $relatedRequests = [
            StorePelunasanPiutangDetailRequest::class
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
            'tglbukti' => 'Tanggal Bukti',
            'alatbayar' => 'alat bayar',
            'bayar.*' => 'Nominal Bayar',
            'keterangan.*' => 'keterangan'
        ];
        
        return $attributes;
    }

    public function messages() 
    {
        return [
            'bayar.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
