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
            'agen' => 'required',
            'jenisorder' => 'required',
            'cabang' => 'required',
            'tglbukti' => 'required',
            'keterangan' => 'required',
        ];
        
        return $rules;
    }
    
    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tanggal Bukti',
            'keterangan' => 'Keterangan',
            'tglterima' => 'Tanggal Terima',
            'jenisorder' => 'Jenis Order'
        ];

        return $attributes;
    }
}
