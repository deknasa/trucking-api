<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class UpdatePenerimaanGiroHeaderRequest extends FormRequest
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
                "required",
                new DateTutupBuku()
            ],
            'pelanggan' => 'required',
            'diterimadari' => 'required',
            'tgllunas' => 'required'
        ];
        $relatedRequests = [
            UpdatePenerimaanGiroDetailRequest::class
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
            'diterimadari' => 'Diterima Dari',
            'tgllunas' => 'Tanggal Lunas',
            'tgljatuhtempo.*' => 'Tanggal jatuh tempo',
            'keterangan_detail.*' => 'Keterangan',
            'bank.*' => 'bank',
            'bankpelanggan.*' => 'bank pelanggan',
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
