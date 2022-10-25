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
            'keterangan' => 'required',
            'bank' => 'required',
            'agen' => 'required',
            'cabang' => 'required',
            'pelanggan' => 'required',
            'agendetail' => 'required',
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
            'keterangan' => 'Keterangan',
            'bank' => 'Bank',
            'agen' => 'Agen',
            'cabang' => 'Cabang',
            'pelanggan' => 'Pelanggan',
            'agendetail' => 'Agen Detail',
            'bayarppd.*' => 'Nominal Bayar',
            'keterangandetailppd.*' => 'Keterangan',
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
            'bayarppd.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
