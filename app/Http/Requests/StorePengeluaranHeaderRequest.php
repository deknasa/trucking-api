<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateTutupBuku;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistBank;
use App\Rules\ExistAlatBayar;
use App\Rules\ValidasiBankList;
use App\Models\AlatBayar;
use App\Rules\ValidasiTotalDetail;

class StorePengeluaranHeaderRequest extends FormRequest
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
        $rules = [
            'tglbukti' => [
                'required','date_format:d-m-Y',
                new DateTutupBuku(),
                // 'before_or_equal:' . date('d-m-Y'),
            ],
        ];
        $relatedRequests = [
            StorePengeluaranDetailRequest::class
        ];
        
        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }

        $bank_id = $this->bank_id;
        $alatBayar = new AlatBayar();
        if ($bank_id != null && $bank_id != 0) {
            $getAlatBayar = $alatBayar->validateBankWithAlatbayar(request()->bank_id);
            $getAlatBayar = json_decode($getAlatBayar, true);
            $kondisialatbayar = true;
            // dd($getAlatBayar);
            foreach ($getAlatBayar as $item) {
                if ($this->alatbayar_id == $item['id']) {
                    $kondisialatbayar = false;
                }
            }
        }
        // dd($kondisialatbayar);
        $rulesbank_id = [];
        if ($bank_id != '' && $this->bank != '') {
            // dd($kondisialatbayar);
            $rulesbank_id = [
                'bank' => [
                    new ExistBank(),
                    new ValidasiBankList($kondisialatbayar),
                    new ValidasiTotalDetail()
                ]
            ];
        } else if ($bank_id != null) {
            if ($bank_id == 0) {
                $rulesbank_id = [
                    'bank_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistBank(),

                    ]

                ];
            } else {
                if ($this->bank == '') {
                    $rulesbank_id = [
                        'bank' => [
                            'required',
                            new ExistBank(),
                            new ValidasiTotalDetail()
                        ]
                    ];
                }
            }
        } else if ($bank_id == null && $this->bank != '') {
            $rulesbank_id = [
                'bank_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistBank(),
                ]
            ];
        } else {
            $rulesbank_id = [
                'bank' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistBank(),
                ]
            ];
        }

        $alatbayar_id = $this->alatbayar_id;
        $rulesalatbayar_id = [];
        if ($alatbayar_id != '' && $this->alatbayar != '') {
            // dd($kondisialatbayar);
            $rulesalatbayar_id = [
                'alatbayar' => [
                    new ExistAlatBayar(),
                ]
            ];
        } else if ($alatbayar_id != null) {
            if ($alatbayar_id == 0) {
                $rulesalatbayar_id = [
                    'alatbayar_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistAlatBayar(),
                    ]

                ];
            } else {
                if ($this->alatbayar == '') {
                    $rulesalatbayar_id = [
                        'alatbayar' => [
                            'required',
                            new ExistAlatBayar(),
                        ]
                    ];
                }
            }
        } else if ($alatbayar_id == null && $this->alatbayar != '') {
            $rulesalatbayar_id = [
                'alatbayar_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistAlatBayar(),
                ]
            ];
        } else {
            $rulesalatbayar_id = [
                'alatbayar' => [
                    'required',
                    new ExistAlatBayar(),
                ]
            ];
        }

        $rule = array_merge(
            $rules,
            $rulesbank_id,
            $rulesalatbayar_id,
        );

        return $rule;

        

    }

    public function attributes()
    {
        $attributes = [
            'dibayarke' => 'Dibayar Ke',
            'transferkeac' => 'Transfer Ke Account',
            'transferkean' => 'Transfer Ke An.',
            'transferkebank' => 'Transfer Ke Bank',
            'alatbayar' => 'Alat Bayar',
            'tgljatuhtempo.*' => 'Tanggal Jatuh Tempo',
            'nominal_detail.*' => 'Nominal',
            'keterangan_detail.*' => 'Keterangan',
            'ketcoadebet.*' => 'nama perkiraan'
        ];
        $relatedRequests = [
            StorePengeluaranDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $attributes = array_merge(
                $attributes,
                (new $relatedRequest)->attributes()
            );
        }
        return $attributes;
    }

    public function messages() 
    {
        return [
            'nominal_detail.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgljatuhtempo.*.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
