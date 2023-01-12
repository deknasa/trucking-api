<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenerimaanTruckingHeaderRequest extends FormRequest
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
            'penerimaantrucking' => 'required',
            'bank' => 'required',
            'coa' => 'required',
            'penerimaan_nobukti' => 'required',
        ];
        $relatedRequests = [
            UpdatePenerimaanTruckingDetailRequest::class
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
            'penerimaantrucking' => 'Kode Penerimaan',
            'penerimaan_nobukti' => 'Nobukti Penerimaan',
            'supir.*' => 'Supir'
        ];
    }
    
    public function messages() 
    {
        return [
            'nominal.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
