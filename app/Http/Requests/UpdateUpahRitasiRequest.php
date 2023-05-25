<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUpahRitasiRequest extends FormRequest
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
            'kotadari_id' => ['required',Rule::unique('upahritasi')->whereNotIn('id', [$this->id])],
            'kotasampai_id' => ['required',Rule::unique('upahritasi')->whereNotIn('id', [$this->id])],
            'jarak' => 'required|numeric|gt:0',
            'statusaktif' => 'required',
            'tglmulaiberlaku' => ['required','date_format:d-m-Y'],
        ];
        $relatedRequests = [
            UpdateUpahRitasiRincianRequest::class
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
            'tglmulaiberlaku' => 'tanggal mulai berlaku',
            'container.*' => 'container',
            'nominalsupir.*' => 'nominal supir',
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
