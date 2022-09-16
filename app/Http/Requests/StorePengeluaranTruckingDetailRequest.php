<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranTruckingDetailRequest extends FormRequest
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
            // 'supir_id' => 'required|array',
            // 'penerimaantruckingheader_nobukti' => 'required|array',
            // 'nominal' => 'required|array',
            
            'supir' => 'required',
            'penerimaantruckingheader_nobukti' => 'required',
            'nominal' => 'required',
        ];
    }
}
