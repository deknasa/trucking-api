<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranTruckingHeaderRequest extends FormRequest
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
            'pengeluarantrucking_id' => 'required',
            'bank_id' => 'required',
            'coa' => 'required',
            'pengeluaran_nobukti' => 'required',
        ];
        $relatedRequests = [
            StorePengeluaranTruckingDetailRequest::class
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
            
            'tglbukti' => 'Tgl Bukti',
            'pengeluarantrucking_id' => 'Kode Pengeluaran',
            'bank_id' => 'Bank',
            'pengeluaran_nobukti' => 'Nobukti Pengeluaran',
            'supir_id.*' => 'Supir'
        ];
    }
    
    public function messages() 
    {
        return [
            'nominal.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
