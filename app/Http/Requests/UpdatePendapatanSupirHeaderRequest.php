<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePendapatanSupirHeaderRequest extends FormRequest
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
            'bank' => 'required',
            'tgldari' => 'required',
            'tglsampai' => 'required',
            'periode' => 'required'
        ];
        $relatedRequests = [
            UpdatePendapatanSupirDetailRequest::class
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
            'tglbukti' => 'tanggal bukti',
            'tgldari' => 'tanggal dari',
            'tglsampai' => 'tanggal sampai',
            'supir.*' => 'supir',
            'nominal.*' => 'nominal',
            'keterangan_detail.*' => 'keterangan'
        ];
    }

    public function messages()
    {
        return [
            'nominal.*.gt' => 'tidak boleh kosong'
        ];
    }
}
