<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranTruckingRequest extends FormRequest
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
        return [
            'kodepengeluaran' => 'required',
            'format' => 'required'
        ];
    }
    
    public function attributes()
    {
        return [
            'kodepengeluaran' => 'Kode Pengeluaran',
            'keterangan' => 'Keterangan',
            'coa' => 'COA',
            'format' => 'Format Bukti',
        ];
    }
}
