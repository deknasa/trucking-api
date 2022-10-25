<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKasGantungHeaderRequest extends FormRequest
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
            'penerima_id' => 'required',
            'keterangan' => 'required',
            'bank_id' => 'required',
            'tglkaskeluar' => 'required',
        ];
        $relatedRequests = [
            StoreKasGantungDetailRequest::class
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
            'penerima_id' => 'Penerima',
            'keterangan' => 'Keterangan',
            'bank_id' => 'Bank',
            'tglkaskeluar' => 'Tanggal Kas Keluar',
            'nominal.*' => 'Nominal',
            'keterangan_detail.*' => 'Keterangan',
        ];
        
        return $attributes;
    }

    public function messages() 
    {
        return [
            'nominal.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
