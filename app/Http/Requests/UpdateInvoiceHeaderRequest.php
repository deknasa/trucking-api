<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceHeaderRequest extends FormRequest
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
            'tglterima' => 'required',
            'agen_id' => 'required',
            'jenisorder_id' => 'required',
            'cabang_id' => 'required',
            'tglbukti' => 'required',
            'keterangan' => 'required',
        ];

        $relatedRequests = [
            UpdateInvoiceDetailRequest::class
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
            'tglterima' => 'Tanggal Terima',
            'agen_id' => 'Agen',
            'jenisorder_id' => 'Jenis Order',
            'cabang_id' => 'Cabang',
            'sp_id' => 'SP'
        ];

        return $attributes;
    }
}
