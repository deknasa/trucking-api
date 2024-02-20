<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\CheckEditingAtValidation;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\DestroyPenerimaanGiro;
use App\Rules\ExistAgen;

class UpdatePenerimaanGiroHeaderRequest extends FormRequest
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
            // 'id' => new CheckEditingAtValidation(),
            "tglbukti" => [
                "required", 'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-d'),
                new DateTutupBuku()
            ],
            'tgllunas' => 'required',
            'agen' => ['required'],
        ];
        $relatedRequests = [
            UpdatePenerimaanGiroDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                ['id' => new DestroyPenerimaanGiro()],
                $rules,
                (new $relatedRequest)->rules(),
                $rulesAgen_id
            );
        }

        // dd($rules);
        return $rules;
    }

    public function attributes()
    {
        return [
            'tglbukti' => 'Tanggal Bukti',
            'diterimadari' => 'Diterima Dari',
            'tgllunas' => 'Tanggal Lunas',
            'agen' => 'customer',
            'tgljatuhtempo.*' => 'Tanggal jatuh tempo',
            'keterangan_detail.*' => 'Keterangan',
            'bank.*' => 'bank',
            'bankpelanggan.*' => 'bank pelanggan',
            'jenisbiaya.*' => 'jenis biaya'
        ];
    }

    public function messages()
    {
        return [
            'nominal.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgljatuhtempo.*.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
