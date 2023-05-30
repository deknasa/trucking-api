<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\AlatBayar;
use App\Rules\DateTutupBuku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePelunasanPiutangHeaderRequest extends FormRequest
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

        $bank_id = $this->bank_id;
        $rulesBank_id = [];
        if ($bank_id != null) {
            if ($bank_id == 0) {
                $rulesBank_id = [
                    'bank_id' => ['required', 'numeric', 'min:1']
                ];
            }
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1']
            ];
        }
        $agen_id = $this->agen_id;
        $rulesAgen_id = [];
        if ($agen_id != null) {
            if ($agen_id == 0) {
                $rulesAgen_id = [
                    'agen_id' => ['required', 'numeric', 'min:1']
                ];
            }
        } else if ($agen_id == null && $this->agen != '') {
            $rulesAgen_id = [
                'agen_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $alatBayar = new AlatBayar();
        $dataAlatBayar = [];
        $dataKodeAlatBayar = [];
        if ($bank_id != null && $bank_id != 0) {
            $getAlatBayar = $alatBayar->validateBankWithAlatbayar(request()->bank_id);
            $getAlatBayar = json_decode($getAlatBayar, true);
            foreach ($getAlatBayar as $item) {
                $dataAlatBayar[] = $item['id'];
                $dataKodeAlatBayar[] = $item['kodealatbayar'];
            }
        }
        $alatbayar_id = $this->alatbayar_id;
        $rulesAlatBayar_id = [];
        if ($alatbayar_id != null) {
            if ($alatbayar_id == 0) {
                $rulesAlatBayar_id = [
                    'alatbayar_id' => ['required', 'numeric', 'min:1',Rule::in($rulesAlatBayar_id)]
                ];
            }
        } else if ($alatbayar_id == null && $this->alatbayar != '') {
            $rulesAlatBayar_id = [
                'alatbayar_id' => ['required', 'numeric', 'min:1',Rule::in($rulesAlatBayar_id)]
            ];
        }

        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                'date_equals:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'bank' => 'required',
            'agen' => 'required',
            'alatbayar' => ['required', Rule::in($dataKodeAlatBayar)],
        ];
        // dd(request()->alatbayar_id, $dataAlatBayar);
        $relatedRequests = [
            StorePelunasanPiutangDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesBank_id,
                $rulesAgen_id,
                $rulesAlatBayar_id
            );
        }

        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tanggal Bukti',
            'alatbayar' => 'alat bayar',
            'bayar.*' => 'Nominal Bayar',
            'keterangan.*' => 'keterangan'
        ];

        return $attributes;
    }

    public function messages()
    {
        return [
            'piutang_id.required' => 'PIUTANG ' . app(ErrorController::class)->geterror('WP')->keterangan,
            'sisa.*.min' => 'SISA ' . app(ErrorController::class)->geterror('NTM')->keterangan,
            'bayar.*.gt' => app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'bayar.*.numeric' => 'nominal harus ' . app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
