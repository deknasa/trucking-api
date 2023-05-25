<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class UpdatePenerimaanHeaderRequest extends FormRequest
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
                'required','date_format:d-m-Y',
                new DateTutupBuku()
            ],
            // 'pelanggan' => 'required',
            'tgllunas'  => 'required',
            // 'cabang' => 'required',
            // 'statuskas' => 'required',
            'bank'   => 'required',
            // 'noresi' => 'required'
        ];
        $relatedRequests = [
            UpdatePenerimaanDetailRequest::class
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
            // 'statuskas' => 'status kas',
            // 'nowarkat.*' => 'no warkat',
            'tgljatuhtempo.*' => 'tanggal jatuh tempo',
            'nominal_detail.*' => 'nominal',
            'keterangan_detail.*' => 'keterangan detail',
            'ketcoakredit.*' => 'nama perkiraan'
        ];
    }
    public function messages()
    {
        return [
            'nominal_detail.*.gt' => 'nominal wajib di isi',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgljatuhtempo.*.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
