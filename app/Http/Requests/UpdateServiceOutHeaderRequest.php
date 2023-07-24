<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ValidasiDestroyServiceOutHeader;

class UpdateServiceOutHeaderRequest extends FormRequest
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
            "id"=>[new ValidasiDestroyServiceOutHeader()],
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                new DateTutupBuku()
            ],            'trado' => 'required',
            'tglkeluar' => [
                "required",'date_format:d-m-Y',
                new DateTutupBuku()
            ],
        ];
        $relatedRequests = [
            UpdateServiceOutDetailRequest::class
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
            'tglkeluar' => 'tanggal keluar',
            'servicein_nobukti.*' => 'no bukti service in',
            'keterangan_detail.*' => 'keterangan detail'
        ];
    }
    
    public function messages() 
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglkeluar.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
