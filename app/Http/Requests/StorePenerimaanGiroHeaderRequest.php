<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanGiroHeaderRequest extends FormRequest
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
            'pelanggan_id' => 'required',
            'keterangan' => 'required',
            'diterimadari' => 'required',
            'tgllunas' => 'required'
        ];
        $relatedRequests = [
            StorePenerimaanGiroDetailRequest::class
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
        return [
            'tglbukti' => 'Tanggal Bukti',
            'pelanggan_id' => 'Pelanggan',
            'diterimadari' => 'Diterima Dari',
            'tgllunas' => 'Tanggal Lunas',
            'tgljatuhtempo.*' => 'Tanggal jatuh tempo',
            'keterangan_detail.*' => 'Keterangan',
            'bank_id.*' => 'bank',
            'bankpelanggan_id.*' => 'bank pelanggan',
            'jenisbiaya.*' => 'jenis biaya'
        ];
    }

    public function messages()
    {
        return [
            'nominal.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
