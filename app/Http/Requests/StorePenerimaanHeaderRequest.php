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
            'pelanggan_id' => 'required',
            'keterangan' => 'required',
            'tgllunas'  => 'required',
            'cabang_id' => 'required',
            'statuskas' => 'required',
            'bank_id'   => 'required',
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
        return [];
    }
}
