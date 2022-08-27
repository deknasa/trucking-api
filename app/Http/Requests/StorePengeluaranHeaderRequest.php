<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranHeaderRequest extends FormRequest
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
            'cabang_id' => 'required',
            'statusjenistransaksi' => 'required',
            'dibayarke' => 'required',
            'bank_id' => 'required',
            'transferkeac' => 'required',
            'transferkean' => 'required',
            'transferkebank' => 'required',
        ];
        $relatedRequests = [
            StorePengeluaranDetailRequest::class
        ];
        
        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }

        return $rules;

    }
}
