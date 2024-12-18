<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ValidasiDestroyAbsensiSupirApprovalHeader;

class UpdateAbsensiSupirApprovalHeaderRequest extends FormRequest
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
            "id"=>[new ValidasiDestroyAbsensiSupirApprovalHeader()],
            "absensisupir_nobukti" => "required",
            "tglbukti" => [
                "required", 'date_format:d-m-Y',
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
