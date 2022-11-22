<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpahSupirRequest extends FormRequest
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
        $rules =  [
            'kotadari' => 'required',
            'kotasampai' => 'required',
            'zona' => 'required',
            'jarak' => 'required|numeric|gt:0',
            'statusaktif' => 'required',
            'statusluarkota' => 'required',
            'tglmulaiberlaku' => 'required',
            'tglakhirberlaku' => 'required',
        ];
        $relatedRequests = [
            StoreUpahSupirRincianRequest::class
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
            'kotadari' => 'kota dari',
            'kotasampai' => 'kota sampai',
            'statusaktif' => 'status aktif',
            'statusluarkota' => 'status luar kota',
            'tglmulaiberlaku' => 'tanggal mulai berlaku',
            'tglakhirberlaku' => 'tanggal akhir berlaku',
            'container.*' => 'container',
            'statuscontainer.*' => 'container',
            'gajisupir.*' => 'gaji supir',
            'gajikenek.*' => 'gaji kenek',
            'gajikomisi.*' => 'gaji komisi',
            'gajitol.*' => 'gaji tol',
            'liter.*' => 'liter',
        ];
    }

    public function messages()
    {
        return [
            'jarak.gt' => 'Jarak wajib di isi',
            'nominalsupir.*.gt' => 'nominal supir wajib di isi',
            'nominalkenek.*.gt' => 'nominal kenek wajib di isi',
            'nominalkomisi.*.gt' => 'nominal komisi wajib di isi',
            'nominaltol.*.gt' => 'nominal tol wajib di isi',
            'liter.*.gt' => 'liter wajib di isi',
        ];
    }
}
