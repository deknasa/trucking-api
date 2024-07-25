<?php

namespace App\Http\Requests;

use App\Rules\ValidasiKeteranganBiayaExtraSupir;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBiayaExtraSupirDetailRequest extends FormRequest
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
            'keteranganbiaya' => 'required|array',
            'keteranganbiaya.*' => ['required', new ValidasiKeteranganBiayaExtraSupir()],
            'nominal.*' => ['numeric', 'min:0'],
            'nominaltagih.*' => ['numeric', 'min:0']
        ];
    }
}
