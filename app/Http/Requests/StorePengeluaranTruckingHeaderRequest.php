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
        return false;
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

        return $rules;
    }
}
