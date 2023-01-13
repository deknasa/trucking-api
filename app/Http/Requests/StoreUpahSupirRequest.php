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
            // 'zona' => 'required',
            'jarak' => 'required|numeric|gt:0',
            'statusaktif' => 'required',
            'statusluarkota' => 'required',
            'tglmulaiberlaku' => 'required',
            // 'tglakhirberlaku' => 'required',
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
            'nominalsupir.*' => 'nominal supir'
        ];
    }

    public function messages()
    {
        return [
            'jarak.gt' => 'Jarak wajib di isi',
            'nominalsupir.*.gt' => 'nominal supir wajib di isi',
        ];
    }
}
