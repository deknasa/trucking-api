<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StoreSuratPengantarRequest extends FormRequest
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
            'jobtrucking' => 'required',
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'dari' => 'required',
            'sampai' => 'required',
            'statusperalihan' => 'required',
            'statuscontainer' => 'required',
            'trado' => 'required',
            'supir' => 'required',
            'statuslongtrip' => 'required',
            'nosp' => 'required',
            'statusgudangsama' => 'required',
            // 'qtyton' => 'required|numeric|gt:0',
            'gudang' => 'required',
            'statusbatalmuat' => 'required',
            // 'totalton' => 'required|numeric|gt:0',
        ];
    }

    public function attributes()
    {
        return [
            'jobtrucking' => 'job trucking',
            'tglbukti' => 'tgl transaksi',
            'statusperalihan' => 'status peralihan',
            'statuscontainer' => 'status container',
            'statuslongtrip' => 'status longtrip',
            'statusgudangsama' => 'status gudangsama',
            // 'qtyton' => 'QTY ton',
            'statusbatalmuat' => 'status batal muat'
            

        ];
    }

    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
