<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAgen;
use App\Rules\ValidasiDetail;
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
        $agen_id = $this->agen_id;
        $jumlahdetail = $this->jumlahdetail ?? 0;
        $rulesAgen_id = [];
        if ($agen_id != null) {
            $rulesAgen_id = [
                'agen_id' => ['required', 'numeric', 'min:1', new ExistAgen()]
            ];
        } else if ($agen_id == null && $this->agen != '') {
            $rulesAgen_id = [
                'agen_id' => ['required', 'numeric', 'min:1', new ExistAgen()]
            ];
        }
        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'agen' => ['required',
            new ValidasiDetail($jumlahdetail)],
            'tglproses' => 'required|date_format:d-m-Y'
        ];

        $rules = array_merge(
            $rules,
            $rulesAgen_id
        );

        return $rules;
    }
    public function attributes()
    {
        $attributes = [
            'agen' => 'customer',
            'tglbukti' => 'Tanggal Bukti',
        ];

        return $attributes;
    }
    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglproses.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
