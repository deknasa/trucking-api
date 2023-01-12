<?php

namespace App\Http\Requests;

use App\Rules\NotOffDay;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePiutangHeaderRequest extends FormRequest
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
            'tglbukti' => [
                'required',
                new NotOffDay()
            ],
            'agen' => 'required',
        ];

        $relatedRequests = [
            UpdatePiutangDetailRequest::class
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
            'tglbukti' => 'Tanggal',
            'agen' => 'Agen',
            'nominal_detail.*' => 'Nominal',
            'keterangan_detail.*' => 'Keterangan',
        ];
        
        return $attributes;
    }

    public function messages() 
    {
        return [
            'nominal_detail.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
