<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHutangHeaderRequest extends FormRequest
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
            'keterangan' => 'required'
        ];
        $relatedRequests = [
            UpdateHutangDetailRequest::class
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
            'keterangan' => 'Keterangan',
            'tgljatuhtempo.*' => 'Tanggal Jatuh Tempo',
            'total_detail.*' => 'Total',
            'keterangan_detail.*' => 'Keterangan'
        ];

        return $attributes;
    }
    public function messages() 
    {
        return [
            'total_detail.*.gt' => 'Total Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
