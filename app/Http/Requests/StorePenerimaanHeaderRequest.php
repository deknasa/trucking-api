<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAlatBayar;
use App\Rules\ExistBank;
use App\Rules\ExistPelanggan;
use App\Rules\ValidasiTotalDetail;

class StorePenerimaanHeaderRequest extends FormRequest
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
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }
        
        $alatbayar_id = $this->alatbayar_id;
        $rulesAlatbayar_id = [];
        if ($alatbayar_id != null) {
            $rulesAlatbayar_id = [
                'alatbayar_id' => ['required', 'numeric', 'min:1', new ExistAlatBayar()]
            ];
        } else if ($alatbayar_id == null && $this->alatbayar != '') {
            $rulesAlatbayar_id = [
                'alatbayar_id' => ['required', 'numeric', 'min:1', new ExistAlatBayar()]
            ];
        }

        // $pelanggan_id = $this->pelanggan_id;
        // $rulesPelanggan_id = [];
        // if ($pelanggan_id != null) {

        //     $rulesPelanggan_id = [
        //         'pelanggan' => ['required'],
        //         'pelanggan_id' => ['required', 'numeric', 'min:1', new ExistPelanggan()]
        //     ];
        // } else if ($pelanggan_id == null && $this->pelanggan != '') {
        //     $rulesPelanggan_id = [
        //         'pelanggan_id' => ['required', 'numeric', 'min:1', new ExistPelanggan()]
        //     ];
        // }

        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y')
            ],

            'tgllunas'  => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('d-m-Y')
            ],
            // 'cabang' => 'required',
            'alatbayar' => 'required',
            'bank'   => ['required', new ValidasiTotalDetail()],
            // 'noresi' => 'required'
        ];
        $relatedRequests = [
            StorePenerimaanDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesBank_id,
                $rulesAlatbayar_id
            );
        }

        return $rules;
    }
    public function attributes()
    {
        return [
            'tgllunas' => 'tanggal lunas',
            // 'statuskas' => 'status kas',
            // 'nowarkat.*' => 'no warkat',
            'tgljatuhtempo.*' => 'tanggal jatuh tempo',
            'nominal_detail.*' => 'nominal',
            'keterangan_detail.*' => 'keterangan detail',
            'ketcoakredit.*' => 'nama perkiraan',
        ];
    }
    public function messages()
    {
        return [
            'nominal_detail.*.gt' => 'nominal wajib di isi',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgllunas.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgljatuhtempo.*.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
