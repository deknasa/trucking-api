<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StoreInvoiceHeaderRequest extends FormRequest
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
            'tglterima' => 'required|date_format:d-m-Y',
            'agen' => 'required',
            'jenisorder' => 'required',
            'tglbukti' => [
                'required','date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'tgldari' => 'required|date_format:d-m-Y',
            'tglsampai' => 'required|date_format:d-m-Y',
        ];

        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tanggal Bukti',
            'tglterima' => 'Tanggal Terima',
            'jenisorder' => 'Jenis Order',
        ];

        return $attributes;
    }

    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglterima.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
