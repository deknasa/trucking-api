<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\NotOffDay;
use App\Rules\DateTutupBuku;
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
        $agen_id = $this->agen_id;
        $rulesAgen_id = [];
        if ($agen_id != null) {
            if ($agen_id == 0) {
                $rulesAgen_id = [
                    'agen_id' => ['required', 'numeric', 'min:1']
                ];
            }
        } else if ($agen_id == null && $this->agen != '') {
            $rulesAgen_id = [
                'agen_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $rules = [
            'tglbukti' => [
                'required','date_format:d-m-Y',
                new NotOffDay(),
                new DateTutupBuku()
            ],
            'agen' => 'required',
        ];

        $relatedRequests = [
            UpdatePiutangDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesAgen_id
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
            'nominal_detail.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'agen_id.required' => ':attribute ' . app(ErrorController::class)->geterror('HPDL')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
