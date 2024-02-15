<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAbsensiApproval;
use App\Rules\ValidasiNominalAbsensiSupir;
use Illuminate\Support\Facades\DB;

class StoreAbsensiSupirApprovalHeaderRequest extends FormRequest
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
        
        return [
            // "keterangan"=>"required",
            "absensisupir_nobukti"=>['required',
            new ValidasiNominalAbsensiSupir(),
            new ExistAbsensiApproval()
        ],
            'tglbukti' => [
                'required','date_format:d-m-Y',
                new DateTutupBuku()
            ],
        ];
    }
    public function messages() 
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
