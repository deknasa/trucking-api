<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanHeaderRequest extends FormRequest
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
            'diterimadari' => 'required',
            'pelanggan' => 'required',
            'tgllunas'  => 'required',
            'cabang' => 'required',
            'statuskas' => 'required',
            'bank'   => 'required',
            // 'noresi' => 'required'
        ];
        $relatedRequests = [
            StorePenerimaanDetailRequest::class
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
            'tgllunas' => 'tanggal lunas',
            'statuskas' => 'status kas',
            'nowarkat.*' => 'no warkat',
            'tgljatuhtempo.*' => 'tanggal jatuh tempo',
            'nominal_detail.*' => 'nominal',
            'keterangan_detail.*' => 'keterangan detail',
            'coadebet.*' => 'coa debet'
        ];
    }
    public function messages()
    {
        return [
            'nominal_detail.*.gt' => 'nominal wajib di isi'
        ];
    }
}
