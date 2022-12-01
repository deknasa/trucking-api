<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranHeaderRequest extends FormRequest
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
            'pelanggan' => 'required',
            'keterangan' => 'required',
            'cabang' => 'required',
            'statusjenistransaksi' => 'required',
            'dibayarke' => 'required',
            'bank' => 'required',
        ];
        $relatedRequests = [
            StorePengeluaranDetailRequest::class
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
            'statusjenistransaksi' => 'Status Jenis Transaksi',
            'dibayarke' => 'Dibayar Ke',
            'transferkeac' => 'Transfer Ke Account',
            'transferkean' => 'Transfer Ke An.',
            'transferkebank' => 'Transfer Ke Bank',
            'alatbayar.*' => 'Alat Bayar',
            'nowarkat.*' => 'No Warkat',
            'tgljatuhtempo.*' => 'Tanggal Jatuh Tempo',
            'nominal_detail.*' => 'Nominal',
            'keterangan_detail.*' => 'Keterangan',
            'coadebet.*' => 'Coa Debet'
        ];
        $relatedRequests = [
            StorePengeluaranDetailRequest::class
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
