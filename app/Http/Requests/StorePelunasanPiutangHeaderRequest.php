<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\AlatBayar;
use App\Models\Parameter;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAgen;
use App\Rules\ExistAlatBayar;
use App\Rules\ExistBank;
use App\Rules\ValidasiDetail;
use App\Rules\ValidasiNoWarkatPelunasanPiutang;
use App\Rules\ValidasiStatusNotaDebet;
use App\Rules\ValidasiStatusNotaKredit;
use App\Rules\ValidasiNominalSaldo;
use App\Rules\ValidasiStatusPelunasan;
use App\Rules\ValidasiNotaDebetPelunasan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
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

        $jumlahdetail = $this->jumlahdetail ?? 0;

        $bank_id = $this->bank_id;
        $rulesBank_id = [];
        if ($bank_id != null) {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }
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
            $rulesAlatBayar_id = [
                'alatbayar_id' => ['required', 'numeric', 'min:1', Rule::in($dataAlatBayar), new ExistAlatBayar()]
            ];
        } else if ($alatbayar_id == null && $this->alatbayar != '') {
            $rulesAlatBayar_id = [
                'alatbayar_id' => ['required', 'numeric', 'min:1', Rule::in($dataAlatBayar), new ExistAlatBayar()]
            ];
        }
        $alatbayarGiro = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        $rulesNoWarkat = [];
        if (request()->alatbayar_id == $alatbayarGiro->id) {
            $rulesNoWarkat = [
                'nowarkat' => 'required'
            ];
        }
        $parameter = new Parameter();
        $data = $parameter->getcombodata('PELUNASAN', 'PELUNASAN');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }
        // dd('tets');
        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y')
            ],
            'bank' => 'required',
            'notadebet_nobukti' =>  [ new ValidasiNotaDebetPelunasan()],
            'statuspelunasan' =>  ['required', Rule::in($status), new ValidasiStatusPelunasan()],
            'agen' => [
                'required',
                new ValidasiDetail($jumlahdetail),
                new ValidasiStatusNotaDebet(),
                new ValidasiStatusNotaKredit(),
                // new ValidasiNominalSaldo()
            ],
            'tgljatuhtempo' => ['date_format:d-m-Y','after_or_equal:'.request()->tglbukti],
            'alatbayar' => ['required', Rule::in($dataKodeAlatBayar)]
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
                $rulesAlatBayar_id,
                $rulesNoWarkat
            );
        }

        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tanggal Bukti',
            'alatbayar' => 'alat bayar',
            'nowarkat' => 'no warkat',
            'tgljatuhtempo' => 'tgl jatuh tempo',
            'agen' => 'customer',
            'bayar.*' => 'Nominal Bayar',
            'keterangan.*' => 'keterangan'
        ];

        return $attributes;
    }

    public function messages()
    {
        return [
            'sisa.*.min' => 'SISA ' . app(ErrorController::class)->geterror('NTM')->keterangan,
            'bayar.*.gt' => app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'bayar.*.numeric' => 'nominal harus ' . app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
