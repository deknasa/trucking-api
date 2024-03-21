<?php

namespace App\Http\Requests;

use App\Rules\validasiNominalDetail;
use App\Rules\validasiNoWarkatPengeluaran;
use App\Rules\validasiTglJatuhTempoPengeluaran;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePengeluaranDetailRequest extends FormRequest
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
            'tgljatuhtempo' => 'required|array',
            'tgljatuhtempo.*' => ['required','date_format:d-m-Y', new validasiTglJatuhTempoPengeluaran()],
            'nowarkat' => 'array',
            'nowarkat.*' => [new validasiNoWarkatPengeluaran()],
            'nominal_detail' => 'required|array',
            'nominal_detail.*' => ['required', 'numeric', new validasiNominalDetail()],
            'ketcoadebet' => 'required|array',
            'ketcoadebet.*' => 'required',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'ketcoadebet.*' => 'nama perkiraan'
        ];
    }
}
