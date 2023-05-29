<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class UpdateRitasiRequest extends FormRequest
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
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                'date_equals:'.date('d-m-Y'),
                new DateTutupBuku()
            ],
            'statusritasi' => 'required','numeric', 'min:1',
            'suratpengantar_nobukti' => 'required',
            'dari' => 'required','numeric', 'min:1',
            'sampai' => 'required','numeric', 'min:1',
            'trado' => 'required','numeric', 'min:1',
            'supir' => 'required','numeric', 'min:1',
        ];
    }

    public function attributes()
    {
        return [
            'tglbukti' => 'tanggal bukti',
            'statusritasi' => 'status ritasi',
            'suratpengantar_nobukti' => 'No bukti surat pengantar',
        ];
    }
    public function messages() 
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
