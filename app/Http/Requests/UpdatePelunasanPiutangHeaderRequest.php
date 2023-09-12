<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\AlatBayar;
use App\Models\PelunasanPiutangHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ValidasiDestroyPelunasanPiutang;
use App\Rules\ValidasiDetail;
use App\Rules\ValidasiStatusNotaDebet;
use App\Rules\ValidasiStatusNotaKredit;
use Illuminate\Validation\Rule;

class UpdatePelunasanPiutangHeaderRequest extends FormRequest
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

        $jumlahdetail = $this->jumlahdetail ?? 0;
        $pelunasanPiutang = new PelunasanPiutangHeader();
        $getDataPelunasan = $pelunasanPiutang->findAll(request()->id);

        $bank_id = $this->bank_id;
        $rulesBank_id = [];
        if ($bank_id != null) {
            if ($bank_id == 0) {
                $rulesBank_id = [
                    'bank_id' => ['required', 'numeric', 'min:1', Rule::in($getDataPelunasan->bank_id)]
                ];
            }
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', Rule::in($getDataPelunasan->bank_id)]
            ];
        }
        $agen_id = $this->agen_id;
        $rulesAgen_id = [];
        if ($agen_id != null) {
            if ($agen_id == 0) {
                $rulesAgen_id = [
                    'agen_id' => ['required', 'numeric', 'min:1', Rule::in($getDataPelunasan->agen_id)]
                ];
            }
        } else if ($agen_id == null && $this->agen != '') {
            $rulesAgen_id = [
                'agen_id' => ['required', 'numeric', 'min:1', Rule::in($getDataPelunasan->agen_id)]
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
        if ($alatbayar_id != null && $bank_id != 0) {
            if ($alatbayar_id == 0) {
                $rulesAlatBayar_id = [
                    'alatbayar_id' => ['required', 'numeric', 'min:1', Rule::in($getDataPelunasan->alatbayar_id)]
                ];
            }
        } else if ($alatbayar_id == null && $this->alatbayar != '') {
            $rulesAlatBayar_id = [
                'alatbayar_id' => ['required', 'numeric', 'min:1', Rule::in($getDataPelunasan->alatbayar_id)]
            ];
        }

        $rules = [
            'id' => new ValidasiDestroyPelunasanPiutang(),
            'nobukti' => [Rule::in($getDataPelunasan->nobukti)],
            "tglbukti" => [
                "required", 'date_format:d-m-Y',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'bank' => 'required',
            'agen' => [
                'required',
                new ValidasiDetail($jumlahdetail),
                new ValidasiStatusNotaDebet(),
                new ValidasiStatusNotaKredit()
            ],
            'alatbayar' => ['required', Rule::in($dataKodeAlatBayar)],
        ];

        $relatedRequests = [
            UpdatePelunasanPiutangDetailRequest::class
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
            'bayar.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'bayar.*.numeric' => 'nominal harus ' . app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
