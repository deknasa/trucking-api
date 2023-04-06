<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use Illuminate\Foundation\Http\FormRequest;

class StorePindahBukuRequest extends FormRequest
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
                'required',
                new DateTutupBuku()
            ],
            'tgljatuhtempo' => 'required',
            'bankdari' => 'required',
            'bankke' => 'required',
            'alatbayar' => 'required',
            'nominal' => 'required',
            'keterangan' => 'required',
        ];
    }
    
    public function attributes()
    {
        return [
            'tgljatuhtempo' => 'tanggal jatuh tempo',
            'bankdari' => 'bank dari',
            'bankke' => 'bank ke',
            'alatbayar' => 'alat bayar',
        ];
    }
}
