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
            'pengeluarantrucking' => 'required',
            'bank' => 'required',
            'akunpusat' => 'required',
            'pengeluaran_nobukti' => 'required',
        ];
        
        return $rules;
    }
}
