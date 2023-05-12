<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateTutupBuku;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceChargeGandenganHeaderRequest extends FormRequest
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
            'tglbukti' => [
                'required','date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'agen' => 'required',
            'tglproses' => 'required|date_format:d-m-Y'
        ];
    }
    public function messages() 
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglproses.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
