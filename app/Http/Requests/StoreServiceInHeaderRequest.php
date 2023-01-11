<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceInHeaderRequest extends FormRequest
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
            'trado' => 'required',
            'tglmasuk' => 'required'
        ];
        $relatedRequests = [
            StoreServiceInDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }
        
        return $rules;
    }

    public function attributes() {
        return [
            'tglbukti' => 'tanggal bukti',
            'tglmasuk' => 'tanggal masuk',
            'mekanik.*' => 'mekanik',
            'keterangan_detail.*' => 'keterangan detail'
        ];
    }
}
